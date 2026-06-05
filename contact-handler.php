<?php
/**
 * contact-handler.php — receives the contact form, stores the enquiry and
 * relays it to the configured email recipients (1–3) over SMTP.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Honeypot — silently accept bot submissions without doing anything.
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'message' => 'Thanks! We will connect with you soon.']);
    exit;
}

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$mobile  = trim((string) ($_POST['mobile'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $mobile === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please fill in your name, mobile, email and message.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please provide a valid mobile number.']);
    exit;
}

if (empty($_POST['consent_notifications']) || empty($_POST['consent_terms'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please tick both consent checkboxes to continue.']);
    exit;
}

$remoteIp  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// 1) Persist the enquiry so nothing is lost even if email delivery hiccups.
$pdo = db();
if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (name, email, mobile, message, ip, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$name, $email, $mobile, $message, $remoteIp, $userAgent]);
    } catch (Throwable $e) {
        error_log('Failed to store contact message: ' . $e->getMessage());
    }
}

// 2) Email the configured recipients (clamped to a maximum of 3).
$mailCfg    = config('mail');
$recipients = array_slice(array_values(array_filter(array_map('trim', $mailCfg['recipients'] ?? []))), 0, 3);

if (!$recipients) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'No recipient is configured. Please try again later.']);
    exit;
}

$safe = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$html = '<div style="font-family:Arial,Helvetica,sans-serif;color:#0f172a;font-size:15px;line-height:1.6">'
    . '<h2 style="margin:0 0 12px">New enquiry via the SportsbyA website</h2>'
    . '<table cellpadding="6" style="border-collapse:collapse">'
    . '<tr><td style="color:#64748b">Name</td><td><strong>' . $safe($name) . '</strong></td></tr>'
    . '<tr><td style="color:#64748b">Mobile</td><td><strong>' . $safe($mobile) . '</strong></td></tr>'
    . '<tr><td style="color:#64748b">Email</td><td><strong>' . $safe($email) . '</strong></td></tr>'
    . '</table>'
    . '<p style="margin:16px 0 4px;color:#64748b">Message</p>'
    . '<p style="white-space:pre-wrap;margin:0">' . nl2br($safe($message)) . '</p>'
    . '<hr style="border:none;border-top:1px solid #e2e8f0;margin:20px 0">'
    . '<p style="font-size:12px;color:#94a3b8">IP: ' . $safe($remoteIp) . '<br>User-Agent: ' . $safe($userAgent)
    . '<br>Time: ' . $safe(date('c')) . '</p>'
    . '</div>';

$text = "New enquiry via the SportsbyA website\n\n"
    . "Name: {$name}\nMobile: {$mobile}\nEmail: {$email}\n\nMessage:\n{$message}\n\n"
    . "IP: {$remoteIp}\nUser-Agent: {$userAgent}\nTime: " . date('c');

$mailer = new Mailer($mailCfg);
$sent   = $mailer->send($recipients, $mailCfg['subject'] ?? 'New enquiry', $html, $text, $email, $name);

if (!$sent) {
    error_log('Contact email failed: ' . $mailer->error);
    http_response_code(502);
    echo json_encode(['ok' => false, 'message' => 'We could not send your message right now. Please email us directly or try again shortly.']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Thanks! We will connect with you soon.']);
