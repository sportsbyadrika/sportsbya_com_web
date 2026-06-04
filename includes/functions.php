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
