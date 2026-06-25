<?php
require_once __DIR__ . '/auth.php';
require_admin();

$adminCurrent = basename($_SERVER['SCRIPT_NAME'], '.php');
$adminTitle   = $adminTitle ?? 'Admin';

$adminNav = [
    'index'    => ['label' => 'Dashboard',   'icon' => 'bi-speedometer2'],
    'clients'  => ['label' => 'Our Clients', 'icon' => 'bi-people'],
    'blog'     => ['label' => 'Blog',         'icon' => 'bi-journal-text'],
    'receipts' => ['label' => 'Receipts',     'icon' => 'bi-receipt'],
    'payments' => ['label' => 'Payments',     'icon' => 'bi-cash-coin'],
];
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($adminTitle) ?> — SportsbyA Admin</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="min-h-screen bg-slate-100 font-sans text-ink antialiased">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="hidden w-64 shrink-0 flex-col bg-ink text-slate-300 lg:flex">
        <div class="flex h-16 items-center gap-2 px-6 font-bold text-white">
            <img src="/sba-logo.png" alt="" width="32" height="32" class="h-8 w-8 rounded bg-white/10 object-contain">
            SportsbyA <span class="text-brand">Admin</span>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-4">
            <?php foreach ($adminNav as $key => $item): ?>
                <a href="<?= admin_url($key === 'index' ? '' : $key) ?>"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition <?= $adminCurrent === $key ? 'bg-brand text-white' : 'hover:bg-white/10' ?>">
                    <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="border-t border-white/10 p-3">
            <a href="/" target="_blank" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-white/10"><i class="bi bi-box-arrow-up-right"></i> View site</a>
            <a href="<?= admin_url('logout') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-white/10"><i class="bi bi-box-arrow-right"></i> Sign out</a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <!-- Mobile nav -->
                <div class="lg:hidden">
                    <details class="relative">
                        <summary class="grid h-9 w-9 cursor-pointer list-none place-items-center rounded-lg border border-slate-200"><i class="bi bi-list text-xl"></i></summary>
                        <div class="absolute left-0 z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                            <?php foreach ($adminNav as $key => $item): ?>
                                <a href="<?= admin_url($key === 'index' ? '' : $key) ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-50"><i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?></a>
                            <?php endforeach; ?>
                            <a href="<?= admin_url('logout') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-50"><i class="bi bi-box-arrow-right"></i> Sign out</a>
                        </div>
                    </details>
                </div>
                <h1 class="text-lg font-semibold"><?= htmlspecialchars($adminTitle) ?></h1>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-600">
                <i class="bi bi-person-circle text-lg"></i> <?= htmlspecialchars(admin_name()) ?>
            </div>
        </header>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
