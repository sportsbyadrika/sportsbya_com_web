<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Lazily create (and reuse) a PDO connection.
 *
 * Returns null when the database is unreachable or not yet configured so that
 * public pages can degrade gracefully (e.g. show an empty state) instead of
 * throwing a fatal error.
 */
function db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($tried) {
        return $pdo;
    }
    $tried = true;

    $cfg = config('db');
    try {
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Throwable $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

/** Run a SELECT and return all rows, or an empty array if the DB is down. */
function db_all(string $sql, array $params = []): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Query failed: ' . $e->getMessage());
        return [];
    }
}

/** Run a SELECT and return a single row, or null. */
function db_one(string $sql, array $params = []): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        error_log('Query failed: ' . $e->getMessage());
        return null;
    }
}
