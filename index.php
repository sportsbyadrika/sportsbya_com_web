<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle       = 'SportsbyA Tech — Empowering athletes with technology';
$pageDescription = 'SportsbyA Tech builds performance analytics, event management and community platforms for federations, academies and clubs.';

// Latest published content for the homepage previews (degrades gracefully).
$clients = db_all("SELECT name, logo_path, website_url FROM clients WHERE is_active = 1 ORDER BY sort_order ASC, name ASC LIMIT 8");
$posts   = db_all("SELECT title, slug, excerpt, cover_path, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC, id DESC LIMIT 3");

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                    <i class="bi bi-stars text-brand"></i> Sports Technology Partner
                </span>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">
                    Empowering <span class="text-brand">Athletes</span> with Technology
                </h1>
                <p class="mt-5 max-w-xl text-lg text-slate-600">
                    From performance analytics to event management, SportsbyA Tech builds platforms
                    that uplift athletes, coaches and organizations.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= url('contact') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-dark">
                        <i class="bi bi-graph-up-arrow"></i> Request a Demo
                    </a>
                    <a href="<?= url('solutions') ?>" class="inline-flex items-center gap-2 rounded-full border border-brand px-6 py-3 font-semibold text-brand transition hover:bg-brand hover:text-white">
                        <i class="bi bi-grid"></i> Explore Solutions
                    </a>
                </div>
            </div>
            <div class="text-center">
                <img src="<?= url('athelete_performance.png') ?>" alt="Athlete performance dashboard" class="mx-auto rounded-3xl shadow-2xl">
            </div>
        </div>
    </div>
</section>

<!-- KEY OFFERINGS -->
<section class="bg-slate-50 py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight">Our Key Offerings</h2>
            <p class="mt-3 text-slate-600">Purpose-built technology across the athlete and event lifecycle.</p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <?php
            $offerings = [
                ['bi-bar-chart-fill', 'Performance Analytics', 'Track athlete performance through data-driven insights and advanced analytics.'],
                ['bi-people-fill', 'Event Management', 'Seamlessly manage sports events, registrations, scoring and results.'],
                ['bi-globe', 'Community Support', 'Connecting athletes, coaches and institutions for better sports development.'],
            ];
            foreach ($offerings as [$icon, $title, $desc]): ?>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="grid h-13 w-13 place-items-center rounded-2xl bg-brand/10 p-3 text-brand">
                        <i class="bi <?= $icon ?> text-xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold"><?= e($title) ?></h3>
                    <p class="mt-2 text-sm text-slate-600"><?= e($desc) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-10 text-center">
            <a href="<?= url('solutions') ?>" class="inline-flex items-center gap-2 font-semibold text-brand hover:text-brand-dark">
                See all solutions <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CORE CATEGORIES -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">Core Sports Categories</h2>
                <p class="mt-2 text-slate-600">Purpose-built solutions for the federations, schools and clubs we serve.</p>
            </div>
            <a href="<?= url('contact') ?>" class="inline-flex items-center gap-2 rounded-full border border-brand px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand hover:text-white">
                <i class="bi bi-chat-dots"></i> Talk to our team
            </a>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php
            $categories = [
                ['image1.jpeg', 'bi-bullseye', 'Shooting Sports', 'Match-ready scoring, live targets, range management and athlete progression tracking for rifle, pistol and shotgun.'],
                ['image2.jpg', 'bi-trophy', 'School Sports Meet', 'Registrations, heat allocations, results and certificates streamlined for inter-house and inter-school athletics.'],
                ['image3.jpeg', 'bi-trophy', 'Sports Meet', 'Registrations, allocations, results and certificates streamlined for baseball / softball tournaments.'],
            ];
            foreach ($categories as [$img, $icon, $title, $desc]): ?>
                <div class="group relative overflow-hidden rounded-3xl shadow-sm">
                    <img src="<?= url($img) ?>" alt="<?= e($title) ?>" class="h-64 w-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/30 to-transparent"></div>
                    <span class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full bg-ink/80 px-3 py-1.5 text-xs font-medium text-white">
                        <i class="bi <?= $icon ?>"></i> <?= e($title) ?>
                    </span>
                    <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                        <h3 class="text-lg font-semibold"><?= e($title) ?></h3>
                        <p class="mt-1 text-sm text-slate-200"><?= e($desc) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($clients): ?>
<!-- CLIENTS PREVIEW -->
<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold tracking-tight">Trusted by teams that take sport seriously</h2>
            <p class="mt-3 text-slate-600">A few of the organizations we partner with.</p>
        </div>
        <div class="mt-10 grid grid-cols-2 items-center gap-6 sm:grid-cols-3 lg:grid-cols-4">
            <?php foreach ($clients as $c): ?>
                <?php $logo = $c['logo_path'] ? url($c['logo_path']) : null; ?>
                <div class="flex items-center justify-center rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <?php if ($logo): ?>
                        <img src="<?= e($logo) ?>" alt="<?= e($c['name']) ?>" class="max-h-12 w-auto object-contain">
                    <?php else: ?>
                        <span class="text-sm font-semibold text-slate-600"><?= e($c['name']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 text-center">
            <a href="<?= url('clients') ?>" class="inline-flex items-center gap-2 font-semibold text-brand hover:text-brand-dark">View all clients <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<!-- BLOG PREVIEW -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">From the Blog</h2>
                <p class="mt-2 text-slate-600">Insights on sports technology, analytics and event operations.</p>
            </div>
            <a href="<?= url('blog') ?>" class="inline-flex items-center gap-2 font-semibold text-brand hover:text-brand-dark">All posts <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <?php foreach ($posts as $p): ?>
                <a href="<?= url('blog/' . $p['slug']) ?>" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 transition hover:shadow-md">
                    <div class="aspect-video overflow-hidden bg-slate-100">
                        <?php if ($p['cover_path']): ?>
                            <img src="<?= url($p['cover_path']) ?>" alt="<?= e($p['title']) ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <?php else: ?>
                            <div class="grid h-full place-items-center text-brand/40"><i class="bi bi-journal-text text-4xl"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400"><?= e(nice_date($p['published_at'])) ?></p>
                        <h3 class="mt-2 text-lg font-semibold group-hover:text-brand"><?= e($p['title']) ?></h3>
                        <p class="mt-2 text-sm text-slate-600"><?= e($p['excerpt'] ?: '') ?></p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand">Read more <i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-ink px-6 py-12 text-center text-white sm:px-12">
            <h2 class="text-3xl font-extrabold tracking-tight">Ready to build better sports experiences?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-slate-300">Tell us about your sport, goals or the problem you're solving and we'll schedule a demo or follow-up call.</p>
            <a href="<?= url('contact') ?>" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark">
                <i class="bi bi-send"></i> Get in touch
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
