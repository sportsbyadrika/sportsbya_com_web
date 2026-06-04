<?php
/**
 * One-time installer: creates the database tables and the first admin user.
 *
 * 1. Configure your database in includes/config.php (or via environment vars).
 * 2. Visit /install in your browser.
 * 3. DELETE this file afterwards.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$messages = [];
$errors   = [];
$done     = false;

$pdo = db();
if ($pdo === null) {
    $errors[] = 'Cannot connect to the database. Check your credentials in includes/config.php.';
} else {
    try {
        // Create tables.
        $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
        if ($sql === false) {
            throw new RuntimeException('Could not read sql/schema.sql.');
        }
        // Execute statement-by-statement (split on semicolons at line ends).
        foreach (array_filter(array_map('trim', preg_split('/;\s*\n/', $sql))) as $stmt) {
            if ($stmt === '' || str_starts_with($stmt, '--')) {
                continue;
            }
            $pdo->exec($stmt);
        }
        $messages[] = 'Database tables are ready.';

        // Seed the first admin user if none exists.
        $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        if ($count === 0) {
            $seed = config('admin_seed');
            $hash = password_hash($seed['password'], PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO admin_users (username, password_hash, name) VALUES (?, ?, ?)')
                ->execute([$seed['username'], $hash, $seed['name']]);
            $messages[] = 'Admin user "' . htmlspecialchars($seed['username']) . '" created. '
                . 'Sign in and change the password configuration as needed.';
        } else {
            $messages[] = 'An admin user already exists — skipped seeding.';
        }

        // Ensure upload directories exist.
        foreach (['uploads/clients', 'uploads/blog'] as $d) {
            $path = __DIR__ . '/' . $d;
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
        $messages[] = 'Upload directories are ready.';
        $done = true;
    } catch (Throwable $e) {
        $errors[] = 'Installation failed: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Install — SportsbyA Tech</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 font-sans text-ink">
    <div class="w-full max-w-lg px-4">
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <h1 class="text-xl font-bold">SportsbyA Tech — Installer</h1>

            <?php foreach ($errors as $e): ?>
                <p class="mt-4 rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700"><?= $e ?></p>
            <?php endforeach; ?>
            <?php foreach ($messages as $m): ?>
                <p class="mt-3 rounded-lg bg-green-50 px-4 py-2.5 text-sm text-green-700"><i class="bi bi-check-circle"></i> <?= $m ?></p>
            <?php endforeach; ?>

            <?php if ($done): ?>
                <div class="mt-6 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <strong>Important:</strong> delete <code>install.php</code> from the server now for security.
                </div>
                <a href="/admin/login" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 font-semibold text-white">Go to admin login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
