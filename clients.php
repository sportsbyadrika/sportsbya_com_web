<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle       = 'Our Clients — SportsbyA Tech';
$pageDescription = 'Federations, academies, schools and clubs that partner with SportsbyA Tech.';

$clients = db_all("SELECT name, logo_path, website_url, description FROM clients WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                <i class="bi bi-award text-brand"></i> Our Clients
            </span>
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Trusted by teams that take sport seriously</h1>
            <p class="mt-5 text-lg text-slate-600">From national federations to neighbourhood clubs, organizations rely on SportsbyA Tech to run events, track performance and grow their communities.</p>
        </div>
    </div>
</section>

<!-- CLIENTS GRID -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <?php if ($clients): ?>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($clients as $c): ?>
                    <?php
                    $logo = $c['logo_path'] ? url($c['logo_path']) : null;
                    $hasLink = !empty($c['website_url']);
                    $tag = $hasLink ? 'a' : 'div';
                    ?>
                    <<?= $tag ?> <?= $hasLink ? 'href="' . e($c['website_url']) . '" target="_blank" rel="noopener"' : '' ?>
                        class="group flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition hover:shadow-md">
                        <div class="flex h-20 items-center">
                            <?php if ($logo): ?>
                                <img src="<?= e($logo) ?>" alt="<?= e($c['name']) ?>" class="max-h-16 w-auto object-contain">
                            <?php else: ?>
                                <span class="text-lg font-bold text-slate-700"><?= e($c['name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-4 font-semibold group-hover:text-brand"><?= e($c['name']) ?></h2>
                        <?php if (!empty($c['description'])): ?>
                            <p class="mt-2 text-sm text-slate-600"><?= e($c['description']) ?></p>
                        <?php endif; ?>
                        <?php if ($hasLink): ?>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand">Visit website <i class="bi bi-box-arrow-up-right"></i></span>
                        <?php endif; ?>
                    </<?= $tag ?>>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mx-auto max-w-md rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand/10 text-brand"><i class="bi bi-people text-2xl"></i></div>
                <h2 class="mt-4 text-lg font-semibold">Client showcase coming soon</h2>
                <p class="mt-2 text-sm text-slate-600">We're putting together the organizations we work with. In the meantime, we'd love to add you to the list.</p>
                <a href="<?= url('contact') ?>" class="mt-5 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-chat-dots"></i> Become a client</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
