<?php
require_once __DIR__ . '/auth.php';

if (admin_logged_in()) {
    header('Location: ' . admin_url(''));
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (db() === null) {
            $error = 'Database is not configured. Please set your credentials in includes/config.php.';
        } elseif (admin_attempt_login($username, $password)) {
            header('Location: ' . admin_url(''));
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — SportsbyA Admin</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 font-sans text-ink antialiased">
    <div class="w-full max-w-sm px-4">
        <div class="mb-6 flex items-center justify-center gap-2 text-xl font-bold">
            <img src="/sba-logo.png" alt="" width="40" height="40" class="h-10 w-10 rounded-lg object-contain">
            SportsbyA <span class="text-brand">Admin</span>
        </div>
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <h1 class="text-lg font-semibold">Sign in</h1>
            <p class="mt-1 text-sm text-slate-500">Manage clients and blog posts.</p>

            <?php if ($error): ?>
                <p class="mt-4 rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" id="username" name="username" required autofocus
                           class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                </div>
                <button type="submit" class="w-full rounded-full bg-brand px-5 py-2.5 font-semibold text-white transition hover:bg-brand-dark">Sign in</button>
            </form>
        </div>
        <p class="mt-6 text-center text-sm text-slate-400"><a href="/" class="hover:text-brand">← Back to website</a></p>
    </div>
</body>
</html>
