<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'About Us — SportsbyA Tech';
$pageDescription = 'Meet the purpose-driven builders of sport-tech for India behind SportsByA Tech (OPC) Private Limited.';

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                    <i class="bi bi-people-fill text-brand"></i> About Us
                </span>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Purpose-driven builders of sport-tech for India</h1>
                <p class="mt-5 text-lg text-slate-600">
                    SportsbyA Tech brings together elite athletes, technologists and product leaders who
                    understand the realities of grassroots sport. We combine lived experience as athletes
                    with product strategy and community building to craft athlete-first technology.
                </p>
                <p class="mt-3 text-sm text-slate-500">Operated by <?= e(config('site')['legal_name']) ?>.</p>
            </div>
            <div class="text-center">
                <img src="<?= url('image1.jpeg') ?>" alt="Athletes in action" class="mx-auto h-80 w-full rounded-3xl object-cover shadow-2xl">
            </div>
        </div>
    </div>
</section>

<!-- LEADERSHIP -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Leadership</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">The faces behind SportsbyA Tech</h2>
        </div>
        <div class="mt-12 grid gap-8 md:grid-cols-2">
            <?php
            $leaders = [
                ['adrika.jpg', 'Adrika Narayanan', 'Founder & Director', 'Adrika is a renowned pistol shooter who represented Kerala at the National Games 2025 while pursuing her B.Tech at the Government Engineering College, Barton Hill, Thiruvananthapuram. She brings the athlete\'s perspective to every product sprint.', 'https://www.linkedin.com/in/adrika-narayanan-5419a5275/'],
            ];
            foreach ($leaders as [$img, $name, $role, $bio, $linkedin]): ?>
                <article class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                        <img src="<?= url($img) ?>" alt="<?= e($name) ?>" class="h-28 w-28 rounded-2xl object-cover ring-4 ring-brand/20">
                        <div>
                            <span class="inline-flex rounded-full bg-brand/10 px-3 py-1 text-xs font-semibold text-brand"><?= e($role) ?></span>
                            <h3 class="mt-2 text-xl font-bold"><?= e($name) ?></h3>
                            <p class="mt-2 text-sm text-slate-600"><?= e($bio) ?></p>
                            <a href="<?= e($linkedin) ?>" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-sky2 hover:text-brand"><i class="bi bi-linkedin"></i> Connect on LinkedIn</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SUBJECT MATTER EXPERTS -->
<section class="bg-slate-50 py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Consultant Subject Matter Experts</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Grounded expertise from range to field</h2>
            <p class="mt-3 text-slate-600">Our SME network coaches, analyses and validates every feature so the platform mirrors day-to-day realities across disciplines.</p>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <?php
            $experts = [
                ['Shooting Sports', 'H/Capt. R Pandyan (Retd.)', 'Former Coach Indian Shooting Team & ISSF "B" Judge and Coach helping us validate biomechanics dashboards, trigger analytics and call-room workflows.'],
                ['Shooting Sports', 'Mr. Vipindas V', 'ISSF Judge "Class B" & ISSF Coach "Class D", supporting us in rifle shooting and event management.'],
                ['Athletics', 'Mr. Huny Subramanion R', 'Baseball, softball and athletics coach helping us refine our event workflows.'],
            ];
            foreach ($experts as [$tag, $name, $desc]): ?>
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <span class="inline-flex rounded-full bg-brand/10 px-3 py-1 text-xs font-semibold text-brand"><?= e($tag) ?></span>
                    <h3 class="mt-3 font-semibold"><?= e($name) ?></h3>
                    <p class="mt-2 text-sm text-slate-600"><?= e($desc) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COLLABORATE CTA -->
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-ink px-6 py-12 text-white sm:px-12">
            <div class="grid items-center gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-extrabold tracking-tight">Want to collaborate with our experts?</h2>
                    <p class="mt-3 text-slate-300">Share your challenge — competition data capture, talent pathways or athlete wellness — and we'll assemble the right specialist pod.</p>
                </div>
                <div class="lg:text-right">
                    <a href="<?= url('contact') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-envelope"></i> Email the team</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
