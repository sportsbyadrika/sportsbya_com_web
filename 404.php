<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = 'Page not found — SportsbyA Tech';
require __DIR__ . '/includes/header.php';
?>
<section class="py-24">
    <div class="mx-auto max-w-xl px-4 text-center sm:px-6 lg:px-8">
        <p class="text-6xl font-extrabold text-brand">404</p>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Page not found</h1>
        <p class="mt-3 text-slate-600">The page you're looking for may have been moved or no longer exists.</p>
        <a href="<?= url('') ?>" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-house"></i> Back home</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
