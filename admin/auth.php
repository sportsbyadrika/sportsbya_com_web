<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('SBA_ADMIN');
    session_start();
}

/** Is an admin currently logged in? */
function admin_logged_in(): bool
{
    return !empty($_SERVER) && !empty($_SESSION['admin_id']);
}

/** Require a logged-in admin; redirect to login otherwise. */
function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: ' . admin_url('login'));
        exit;
    }
}

/** Current admin display name. */
function admin_name(): string
{
    return (string) ($_SESSION['admin_name'] ?? 'Admin');
}

/** Build an admin URL (clean, no .php). */
function admin_url(string $path = ''): string
{
    return '/admin/' . ltrim($path, '/');
}

/** Attempt to log an admin in. Returns true on success. */
function admin_attempt_login(string $username, string $password): bool
{
    $row = db_one('SELECT id, username, password_hash, name FROM admin_users WHERE username = ? LIMIT 1', [$username]);
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int) $row['id'];
    $_SESSION['admin_name'] = $row['name'] ?: $row['username'];
    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** ---- CSRF ---- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

/**
 * Handle an uploaded image. Returns the stored web path (e.g. uploads/clients/x.png)
 * or null if no file was uploaded. Throws RuntimeException on validation errors.
 */
function admin_handle_upload(string $field, string $subdir): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Image must be 4 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, GIF or SVG images are allowed.');
    }

    $dir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create upload directory.');
    }

    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return 'uploads/' . $subdir . '/' . $name;
}

/**
 * Sanitise admin-authored HTML for blog bodies: allow a safe subset of tags,
 * strip scripts/styles/event handlers and javascript: URLs.
 */
function sanitize_html(string $html): string
{
    // Remove script/style blocks entirely.
    $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
    // Strip inline event handlers (on*="...") and javascript: URLs.
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/i', '$1=$2#$2', $html) ?? $html;

    $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><img><hr><span><code><pre>';
    return strip_tags($html, $allowed);
}
