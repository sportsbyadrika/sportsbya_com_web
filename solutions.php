<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Solutions — SportsbyA Tech';
$pageDescription = 'Performance analytics, event management and community platforms built for every sport, federation, academy and club.';

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                <i class="bi bi-grid text-brand"></i> Solutions
            </span>
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Platforms built for every sport and scale</h1>
            <p class="mt-5 text-lg text-slate-600">
                Whether you run a national shooting championship, a multi-sport federation or a
                neighbourhood club, our products cover the full journey — registrations, scoring,
                analytics, payments and communication.
            </p>
        </div>
    </div>
</section>

<!-- KEY OFFERINGS WITH MODULES -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight">Our Key Offerings</h2>
            <p class="mt-3 text-slate-600">Deep, sport-specific modules across performance, events and community.</p>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            <?php
            $offerings = [
                ['bi-bar-chart-fill', 'Performance Analytics', 'Track athlete performance through data-driven insights.', [
                    'Data capture (wearables / IoT, video, manual logs)',
                    'Sport-specific KPI library (series / inner-10, group size, sight corrections)',
                    'Session planning & load tracking (RPE, volume, intensity)',
                    'Technique analysis (video tagging, heatmaps, shot charts)',
                    'Benchmarks & goal tracking (season PRs, targets)',
                    'Dashboards for coach / athlete & mobile view',
                    'Alerts & anomalies (attendance, performance dips)',
                    'Reports & exports (PDF / Excel), share links',
                    'Integrations & API (event results, live scoring)',
                    'Privacy & consent (roles, retention)',
                ]],
                ['bi-people-fill', 'Event Management', 'Manage events, registrations and results end-to-end.', [
                    'Registrations & eligibility (individual / team, age groups, KYC / consents)',
                    'Seeding, heats & lane / relay allocation (draws, firing points, conflict checks)',
                    'Scheduling & call room (timetable, venue allocation, marshaling)',
                    'Officials & accreditation (roles / shifts, QR / RFID badges)',
                    'Live scoring, results & certificates (tie-breaks, medal tally, PDFs)',
                ]],
                ['bi-globe', 'Community Support', 'Connect athletes, coaches and institutions.', [
                    'Association / club MIS (memberships, dues, teams, assets, notices)',
                    'Athlete community (profiles, verification, achievements, mentoring)',
                    'Find sports near me (facilities, coaches, bookings)',
                    'Knowledge hub (rules, drills, training plans, nutrition)',
                    'Mentor–mentee matching (coaches, experts, sessions)',
                    'Safeguarding & grievances (reporting, escalation, tracking)',
                ]],
            ];
            foreach ($offerings as [$icon, $title, $desc, $modules]): ?>
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="grid h-13 w-13 place-items-center rounded-2xl bg-brand/10 p-3 text-brand"><i class="bi <?= $icon ?> text-xl"></i></div>
                    <h3 class="mt-4 text-lg font-semibold"><?= e($title) ?></h3>
                    <p class="mt-2 text-sm text-slate-600"><?= e($desc) ?></p>
                    <div class="mt-4 rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Modules</p>
                        <ul class="mt-2 space-y-1.5 text-sm text-slate-600">
                            <?php foreach ($modules as $m): ?>
                                <li class="flex items-start gap-2"><i class="bi bi-check2-circle mt-0.5 text-green-500"></i><span><?= e($m) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PLATFORM FIT -->
<section class="bg-slate-50 py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight">Platform fit by sport and scale</h2>
            <p class="mt-3 text-slate-600">Pick the product that aligns with your operations and growth goals.</p>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            <?php
            $platforms = [
                ['bi-buildings', 'Sports Infrastructure', 'sportsinfrax.com', 'https://sportsinfrax.com', 'Manage sports facilities, venues and assets with online discovery, bookings and maintenance.', [
                    'Facility & venue directory (grounds, courts, ranges, pools)',
                    'Online slot booking with e-payments and reminders',
                    'Membership, passes and access management',
                    'Maintenance, asset and staff scheduling',
                    'Utilization, occupancy and revenue analytics',
                ]],
                ['bi-trophy', 'Multi-Sport MIS', 'sportsmis.com', 'https://sportsmis.com', 'A single operating system for federations, leagues and academies across every sport.', [
                    'Online registrations with e-payments and WhatsApp confirmations',
                    'Event draws, schedules and live results dashboards',
                    'Digital certificates and automated participant communication',
                    'Analytics for participation, revenues and performance trends',
                    'Extensible APIs for websites, apps and broadcast overlays',
                ]],
                ['bi-people', 'Clubs & Wellness', 'dewroute.com', 'https://dewroute.com/', 'Ideal for small clubs, gyms, institutions, healthcare programs and community initiatives.', [
                    'Membership management with plans, renewals and invoices',
                    'Schedules and services for training, delivery or sessions',
                    'Billing with e-payments, receipts and reminders',
                    'Operational reports for attendance, collections, utilization',
                    'Member communication via SMS and WhatsApp',
                ]],
            ];
            foreach ($platforms as [$icon, $title, $domain, $link, $desc, $points]): ?>
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand/10 text-brand"><i class="bi <?= $icon ?> text-lg"></i></div>
                        <div>
                            <h3 class="font-semibold leading-tight"><?= e($title) ?></h3>
                            <p class="text-xs text-slate-500"><?= e($domain) ?></p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-600"><?= e($desc) ?></p>
                    <ul class="mt-4 flex-1 space-y-2 text-sm text-slate-600">
                        <?php foreach ($points as $pt): ?>
                            <li class="flex items-start gap-2"><i class="bi bi-check2-circle mt-0.5 text-green-500"></i><span><?= e($pt) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= e($link) ?>" target="_blank" rel="noopener" class="mt-6 inline-flex items-center justify-center gap-2 rounded-full border border-brand px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand hover:text-white">
                        Visit <?= e($domain) ?> <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ANALYTICS HIGHLIGHT -->
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-sky2/10 px-4 py-1.5 text-sm font-medium text-sky2">
                    <i class="bi bi-graph-up-arrow"></i> Analytics
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight">Actionable dashboards built with athletes and coaches</h2>
                <p class="mt-4 text-slate-600">From session-level KPIs to federation-grade summaries, we combine performance, readiness and event data into role-based dashboards.</p>
                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <?php
                    $analytics = [
                        ['bi-bullseye', 'Shot-by-shot performance', 'Series aggregation, inner-10 count, group size and sight-correction logs.'],
                        ['bi-activity', 'Training load & readiness', 'RPE, session volumes, recovery inputs and readiness scores.'],
                        ['bi-clipboard-data', 'Program & event reporting', 'Live scoring, medal tallies and federation-ready exports.'],
                    ];
                    foreach ($analytics as [$icon, $t, $d]): ?>
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-sky2/10 text-sky2"><i class="bi <?= $icon ?>"></i></div>
                            <h3 class="mt-3 text-sm font-semibold"><?= e($t) ?></h3>
                            <p class="mt-1 text-xs text-slate-500"><?= e($d) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="rounded-3xl bg-white p-4 shadow-2xl ring-1 ring-slate-100">
                <img src="<?= url('athelete_performance.png') ?>" alt="Athlete performance dashboard" class="rounded-2xl">
            </div>
        </div>
    </div>
</section>

<!-- INTEGRATIONS -->
<section class="bg-slate-50 py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">Built-in integrations that remove friction</h2>
                <p class="mt-4 text-slate-600">Connect to the services you already use to keep registrations, payments and communication seamless.</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600">
                    <li class="flex items-start gap-3"><i class="bi bi-credit-card-2-front-fill mt-0.5 text-brand"></i><span>e-Payment gateways for cards, UPI and net banking.</span></li>
                    <li class="flex items-start gap-3"><i class="bi bi-chat-dots-fill mt-0.5 text-green-500"></i><span>SMS and WhatsApp messaging for confirmations and reminders.</span></li>
                    <li class="flex items-start gap-3"><i class="bi bi-hdd-network-fill mt-0.5 text-sky2"></i><span>APIs for websites, mobile apps and scoring systems.</span></li>
                    <li class="flex items-start gap-3"><i class="bi bi-gear-wide-connected mt-0.5 text-amber-500"></i><span>In progress: DigiLocker issuance and ONDC integrations.</span></li>
                </ul>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <?php
                $ints = [
                    ['bi-receipt', 'Payments & Billing', 'Instant fee collection, refunds and receipts.'],
                    ['bi-whatsapp', 'Conversational updates', 'WhatsApp & SMS flows for status and alerts.'],
                    ['bi-journal-check', 'Certificates & Docs', 'Digital certificates and DigiLocker push.'],
                    ['bi-basket', 'Commerce-ready', 'ONDC-compatible workflows under development.'],
                ];
                foreach ($ints as [$icon, $t, $d]): ?>
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                        <div class="grid h-11 w-11 place-items-center rounded-xl bg-brand/10 text-brand"><i class="bi <?= $icon ?>"></i></div>
                        <h3 class="mt-3 text-sm font-semibold"><?= e($t) ?></h3>
                        <p class="mt-1 text-xs text-slate-500"><?= e($d) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-ink px-6 py-12 text-center text-white sm:px-12">
            <h2 class="text-3xl font-extrabold tracking-tight">Ready to launch your program?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-slate-300">Share your objectives and we'll assemble a rollout covering onboarding, data migration and integrations.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="<?= url('contact') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-send"></i> Request a demo</a>
                <a href="mailto:<?= e(config('site')['email']) ?>" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10"><i class="bi bi-envelope"></i> Write to us</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
