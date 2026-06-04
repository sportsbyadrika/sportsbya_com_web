<?php
require_once __DIR__ . '/functions.php';

$site    = config('site');
$current = current_page();

/** @var string $pageTitle */
$pageTitle = $pageTitle ?? $site['name'];
/** @var string $pageDescription */
$pageDescription = $pageDescription ?? 'SportsbyA Tech — empowering athletes with data analytics, performance tracking, and event management solutions.';

$nav = [
    'solutions' => ['label' => 'Solutions',   'href' => url('solutions')],
    'clients'   => ['label' => 'Our Clients', 'href' => url('clients')],
    'blog'      => ['label' => 'Blog',         'href' => url('blog')],
    'about'     => ['label' => 'About Us',     'href' => url('about')],
    'contact'   => ['label' => 'Contact',      'href' => url('contact')],
];
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="icon" href="<?= url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-white text-ink font-sans antialiased">
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-100 shadow-sm">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="<?= url('') ?>" class="flex items-center gap-2 font-bold text-lg">
                    <img src="<?= url('sba-logo.png') ?>" alt="SportsbyA Tech logo" width="40" height="40" class="h-10 w-10 rounded-lg object-contain">
                    <span>SportsbyA <span class="text-brand">Tech</span></span>
                </a>

                <!-- Desktop nav -->
                <div class="hidden lg:flex items-center gap-8">
                    <?php foreach ($nav as $key => $item): ?>
                        <a href="<?= e($item['href']) ?>"
                           class="text-sm font-medium transition hover:text-brand <?= $current === $key ? 'text-brand' : 'text-slate-700' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('contact') ?>"
                       class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark">
                        <i class="bi bi-rocket-takeoff"></i> Get Started
                    </a>
                </div>

                <!-- Mobile toggle -->
                <button type="button" id="navToggle" aria-label="Toggle navigation"
                        class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-700 hover:bg-slate-100">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>

            <!-- Mobile nav -->
            <div id="mobileNav" class="hidden lg:hidden pb-4">
                <div class="flex flex-col gap-1">
                    <?php foreach ($nav as $key => $item): ?>
                        <a href="<?= e($item['href']) ?>"
                           class="rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-slate-50 <?= $current === $key ? 'text-brand' : 'text-slate-700' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('contact') ?>"
                       class="mt-2 inline-flex items-center justify-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-semibold text-white">
                        <i class="bi bi-rocket-takeoff"></i> Get Started
                    </a>
                </div>
            </div>
        </nav>
    </header>
    <main>
