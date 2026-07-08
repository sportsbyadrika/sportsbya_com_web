<?php
/**
 * Central configuration for the SportsbyA Tech website.
 *
 * IMPORTANT: Update the database, SMTP and recipient values below for your
 * hosting environment. Values can also be supplied through environment
 * variables (handy for cPanel "Environment Variables" or .env style setups);
 * any environment variable that is set takes precedence over the defaults here.
 */

declare(strict_types=1);

$env = static function (string $key, $default) {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

return [
    'site' => [
        'name'       => 'SportsbyA Tech',
        'legal_name' => 'SportsByA Tech (OPC) Private Limited',
        'tagline'    => 'Empowering athletes with technology',
        'email'      => $env('SITE_EMAIL', 'info@sportsbya.com'),
        'phone'      => $env('SITE_PHONE', '+91 9388 7788 85'),
        'address'    => 'Thiruvananthapuram, Kerala, India',
        'hours'      => 'Mon–Sat · 10:00 AM – 5:00 PM IST',
        'social'     => [
            'linkedin'  => 'https://www.linkedin.com/company/sportsbya',
            'instagram' => 'https://www.instagram.com/sportsbya',
            'youtube'   => '',
            'twitter'   => '',
        ],
        // Sister products / brands shown in the footer.
        'brands' => [
            ['label' => 'shootingsports.in', 'url' => 'https://shootingsports.in'],
            ['label' => 'sportsmis.com',     'url' => 'https://sportsmis.com'],
            ['label' => 'sportsinfrax.com',  'url' => 'https://sportsinfrax.com'],
            ['label' => 'dewroute.com',      'url' => 'https://dewroute.com/'],
            ['label' => 'sportsodi.com',     'url' => 'https://sportsodi.com/'],
        ],
    ],

    // ---- MySQL database (used by the Admin-managed Clients & Blog) ----
    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'name'    => $env('DB_NAME', 'CHANGE_ME_DB_NAME'),
        'user'    => $env('DB_USER', 'CHANGE_ME_DB_USER'),
        'pass'    => $env('DB_PASS', 'CHANGE_ME_DB_PASSWORD'),
        'charset' => 'utf8mb4',
    ],

    // ---- Outbound email (contact form) over SMTP ----
    // The production host has the PHP mail() function disabled, so messages are
    // delivered through an authenticated SMTP connection instead.
    'mail' => [
        'host'       => $env('SMTP_HOST', 'smtp.gmail.com'),
        'port'       => (int) $env('SMTP_PORT', '587'),
        'encryption' => $env('SMTP_ENCRYPTION', 'tls'), // 'tls' (587) or 'ssl' (465)
        'username'   => $env('SMTP_USER', 'CHANGE_ME_SMTP_USERNAME'),
        'password'   => $env('SMTP_PASS', 'CHANGE_ME_SMTP_PASSWORD'),
        'from_email' => $env('SMTP_FROM', 'no-reply@sportsbya.com'),
        'from_name'  => 'SportsbyA Website',
        // Contact-form submissions are delivered to these addresses.
        // Provide a minimum of 1 and a maximum of 3 addresses.
        'recipients' => array_filter([
            $env('CONTACT_TO_1', 'info@sportsbya.com'),
            $env('CONTACT_TO_2', ''),
            $env('CONTACT_TO_3', ''),
        ]),
        'subject'    => 'New enquiry via the SportsbyA website',
    ],

    // ---- Seed credentials used once by install.php to create the first admin ----
    'admin_seed' => [
        'username' => $env('ADMIN_USER', 'admin'),
        'password' => $env('ADMIN_PASS', 'ChangeMe@123'),
        'name'     => 'Administrator',
    ],

    // ---- Anti-spam / anti-bot for the public contact form ----
    'security' => [
        // A long random string used to sign form tokens. Leave blank to auto-derive
        // a stable secret from your DB/SMTP settings; set your own for best results.
        'form_secret'   => $env('FORM_SECRET', ''),
        // Minimum seconds a human needs to fill the form (submissions faster than
        // this are treated as bots). Maximum is how long a loaded form stays valid.
        'min_seconds'   => (int) $env('FORM_MIN_SECONDS', '3'),
        'max_seconds'   => (int) $env('FORM_MAX_SECONDS', '7200'),
        // Max contact submissions allowed per IP address per hour.
        'rate_per_hour' => (int) $env('FORM_RATE_PER_HOUR', '6'),
        // Optional Cloudflare Turnstile (free). Leave both blank to disable.
        // Get keys at https://dash.cloudflare.com/?to=/:account/turnstile
        'turnstile_site_key' => $env('TURNSTILE_SITE_KEY', ''),
        'turnstile_secret'   => $env('TURNSTILE_SECRET', ''),
    ],
];
