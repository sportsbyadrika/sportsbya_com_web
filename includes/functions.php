<?php
declare(strict_types=1);

/**
 * Shared helpers used across the public site and the admin area.
 */

/** Return the whole config array, or a single top-level section. */
function config(?string $key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.php';
    }
    if ($key === null) {
        return $cfg;
    }
    return $cfg[$key] ?? null;
}

/** Escape a value for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Current page slug (e.g. "solutions") used for nav active-state. */
function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php');
}

/** Build a root-relative URL. */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** Turn a string into a URL-friendly slug. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'post';
}

/** Short plain-text excerpt from HTML/long text. */
function excerpt(string $text, int $words = 28): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    $parts = explode(' ', $text);
    if (count($parts) <= $words) {
        return $text;
    }
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

/** Format a date string for display. */
function nice_date(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('M j, Y', $ts) : '';
}

/** Secret used to sign anti-bot form tokens (auto-derived if not configured). */
function form_secret(): string
{
    $sec = config('security')['form_secret'] ?? '';
    if (is_string($sec) && strlen($sec) >= 16) {
        return $sec;
    }
    // Stable fallback derived from other config so tokens survive restarts.
    $db   = config('db');
    $mail = config('mail');
    return hash('sha256', 'sba-form|' . ($db['pass'] ?? '') . '|' . ($mail['password'] ?? '') . '|' . ($db['name'] ?? ''));
}

/** Create a [timestamp, signature] pair to embed in a form. */
function form_token(?int $ts = null): array
{
    $ts = $ts ?? time();
    return [$ts, hash_hmac('sha256', (string) $ts, form_secret())];
}

/** Validate a form token: correct signature and within the allowed time window. */
function form_token_valid(string $tsRaw, string $sig, int $min, int $max): bool
{
    if ($tsRaw === '' || !ctype_digit($tsRaw) || $sig === '') {
        return false;
    }
    $ts = (int) $tsRaw;
    $expected = hash_hmac('sha256', (string) $ts, form_secret());
    if (!hash_equals($expected, $sig)) {
        return false;
    }
    $age = time() - $ts;
    return $age >= $min && $age <= $max;
}

/**
 * Verify a Cloudflare Turnstile token.
 * Returns true on success, false on explicit failure, null if the verification
 * request itself could not be completed (caller may choose to fail open).
 */
function turnstile_verify(string $secret, string $token, string $ip): ?bool
{
    $url  = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $post = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $ip]);
    $raw  = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_TIMEOUT        => 8,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $post,
            'timeout' => 8,
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
    }

    if ($raw === false || $raw === null) {
        return null;
    }
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return null;
    }
    return !empty($data['success']);
}
