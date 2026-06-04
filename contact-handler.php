<?php
// contact-handler.php — lightweight email relay for the "Get in Touch" form

declare(strict_types=1);

const MAIL_TO = 'info@shootingsports.in';
const MAIL_SUBJECT = 'New enquiry via SportsbyA site';
const ALLOWED_ORIGINS = ['https://sportsbya.com', 'https://www.sportsbya.com'];

header('Content-Type: application/json; charset=utf-8');

// Basic CORS allowance for prod origins + same-origin fallbacks
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], ALLOWED_ORIGINS, true)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Vary: Origin');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

$remoteIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

$bodyLines = [
    'You have received a new enquiry via the SportsbyA contact form.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Message:',
    $message,
    '',
    '--- Metadata ---',
    'IP: ' . $remoteIp,
    'User-Agent: ' . $userAgent,
    'Time: ' . date('c'),
];

$headers = [
    'From: SportsbyA Website <no-reply@sportsbya.com>',
    'Reply-To: ' . $name . ' <' . $email . '>'
];

$sent = mail(
    MAIL_TO,
    MAIL_SUBJECT,
    implode("\r\n", $bodyLines),
    implode("\r\n", $headers)
);

if (!$sent) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Unable to send email right now.']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Message sent successfully.']);
