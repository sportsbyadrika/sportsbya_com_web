<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Create the finance/settings tables if they don't exist yet.
 * Lets the Receipts & Payments features work without re-running install.php.
 */
function ensure_app_tables(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo = db();
    if (!$pdo) {
        return;
    }
    $done = true;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            k VARCHAR(64) NOT NULL,
            v MEDIUMTEXT NULL,
            PRIMARY KEY (k)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS receipts (
            id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            receipt_no    VARCHAR(60)  NOT NULL,
            receipt_date  DATE         NOT NULL,
            received_from VARCHAR(200) NOT NULL DEFAULT '',
            payment_mode  VARCHAR(60)  NOT NULL DEFAULT '',
            reference     VARCHAR(120) NOT NULL DEFAULT '',
            items         MEDIUMTEXT   NULL,
            total         DECIMAL(12,2) NOT NULL DEFAULT 0,
            notes         VARCHAR(500) NOT NULL DEFAULT '',
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_receipt_date (receipt_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            payment_date DATE         NOT NULL,
            paid_to      VARCHAR(200) NOT NULL DEFAULT '',
            description  VARCHAR(300) NOT NULL DEFAULT '',
            payment_mode VARCHAR(60)  NOT NULL DEFAULT '',
            reference    VARCHAR(120) NOT NULL DEFAULT '',
            amount       DECIMAL(12,2) NOT NULL DEFAULT 0,
            notes        VARCHAR(500) NOT NULL DEFAULT '',
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_payment_date (payment_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        error_log('ensure_app_tables failed: ' . $e->getMessage());
    }
}

/** Read a setting value. */
function setting_get(string $key, ?string $default = null): ?string
{
    $row = db_one('SELECT v FROM settings WHERE k = ?', [$key]);
    return $row ? $row['v'] : $default;
}

/** Create or update a setting value. */
function setting_set(string $key, ?string $value): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }
    $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
        ->execute([$key, $value]);
}

/** Format an amount as Indian Rupees with grouping. */
function inr(float $amount): string
{
    $sign = $amount < 0 ? '-' : '';
    $amount = abs($amount);
    $whole = (int) floor($amount);
    $dec = number_format($amount - $whole, 2, '.', '');
    // Indian digit grouping for the integer part.
    $s = (string) $whole;
    $last3 = substr($s, -3);
    $rest = substr($s, 0, -3);
    if ($rest !== '') {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $grouped = $rest . ',' . $last3;
    } else {
        $grouped = $last3;
    }
    return $sign . '₹' . $grouped . substr($dec, 1);
}

/** Convert a number to Indian-system English words (used on receipts). */
function amount_in_words(float $amount): string
{
    $amount = round($amount, 2);
    $rupees = (int) floor($amount);
    $paise  = (int) round(($amount - $rupees) * 100);

    $out = ucfirst(trim(_inr_words($rupees))) . ' rupees';
    if ($paise > 0) {
        $out .= ' and ' . trim(_inr_words($paise)) . ' paise';
    }
    return $out . ' only';
}

function _inr_words(int $n): string
{
    if ($n === 0) {
        return 'zero';
    }
    $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    $two = static function (int $x) use ($ones, $tens): string {
        if ($x < 20) {
            return $ones[$x];
        }
        return trim($tens[intdiv($x, 10)] . ' ' . $ones[$x % 10]);
    };
    $three = static function (int $x) use ($ones, $two): string {
        $h = intdiv($x, 100);
        $r = $x % 100;
        $s = '';
        if ($h) {
            $s .= $ones[$h] . ' hundred';
        }
        if ($r) {
            $s .= ($h ? ' and ' : '') . $two($r);
        }
        return $s;
    };

    $out = '';
    $crore = intdiv($n, 10000000);
    $n %= 10000000;
    $lakh = intdiv($n, 100000);
    $n %= 100000;
    $thousand = intdiv($n, 1000);
    $hundred = $n % 1000;

    if ($crore) {
        $out .= $three($crore) . ' crore ';
    }
    if ($lakh) {
        $out .= $three($lakh) . ' lakh ';
    }
    if ($thousand) {
        $out .= $three($thousand) . ' thousand ';
    }
    if ($hundred) {
        $out .= $three($hundred);
    }
    return trim($out);
}
