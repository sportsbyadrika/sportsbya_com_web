<?php
require_once __DIR__ . '/includes/db.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug !== '') {
    // -------- Single post --------
    $post = db_one(
        "SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1",
        [$slug]
    );

    if (!$post) {
        http_response_code(404);
        $pageTitle = 'Post not found — SportsbyA Tech';
        require __DIR__ . '/includes/header.php';
        echo '<section class="mx-auto max-w-3xl px-4 py-24 text-center">'
            . '<h1 class="text-3xl font-extrabold">Post not found</h1>'
            . '<p class="mt-3 text-slate-600">The article you\'re looking for may have been moved or unpublished.</p>'
            . '<a href="' . url('blog') . '" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 font-semibold text-white">Back to blog</a>'
            . '</section>';
        require __DIR__ . '/includes/footer.php';
        exit;
    }

    $pageTitle       = e($post['title']) . ' — SportsbyA Tech';
    $pageDescription = $post['excerpt'] ?: excerpt($post['body']);

    require __DIR__ . '/includes/header.php';
    ?>
    <article class="py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <a href="<?= url('blog') ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-dark"><i class="bi bi-arrow-left"></i> All posts</a>
            <p class="mt-6 text-sm font-medium uppercase tracking-wide text-slate-400">
                <?= e(nice_date($post['published_at'])) ?><?= $post['author'] ? ' · ' . e($post['author']) : '' ?>
            </p>
            <h1 class="mt-2 text-4xl font-extrabold tracking-tight"><?= e($post['title']) ?></h1>
            <?php if (!empty($post['excerpt'])): ?>
                <p class="mt-4 text-lg text-slate-600"><?= e($post['excerpt']) ?></p>
            <?php endif; ?>
            <?php if (!empty($post['cover_path'])): ?>
                <img src="<?= url($post['cover_path']) ?>" alt="<?= e($post['title']) ?>" class="mt-8 w-full rounded-2xl object-cover shadow-sm">
            <?php endif; ?>
            <div class="prose-content mt-8 text-slate-700">
                <?= $post['body'] /* admin-authored HTML, sanitized on save */ ?>
            </div>
        </div>
    </article>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// -------- Listing --------
$pageTitle       = 'Blog — SportsbyA Tech';
$pageDescription = 'Insights on sports technology, analytics and event operations from the SportsbyA Tech team.';

$posts = db_all("SELECT title, slug, excerpt, cover_path, author, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC, id DESC");

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-brand/10 to-sky2/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                <i class="bi bi-journal-text text-brand"></i> Blog
            </span>
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Ideas from the field</h1>
            <p class="mt-5 text-lg text-slate-600">Insights on sports technology, analytics, event operations and the athletes we build for.</p>
        </div>
    </div>
</section>

<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <?php if ($posts): ?>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($posts as $p): ?>
                    <a href="<?= url('blog/' . $p['slug']) ?>" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 transition hover:shadow-md">
                        <div class="aspect-video overflow-hidden bg-slate-100">
                            <?php if ($p['cover_path']): ?>
                                <img src="<?= url($p['cover_path']) ?>" alt="<?= e($p['title']) ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="grid h-full place-items-center text-brand/40"><i class="bi bi-journal-text text-4xl"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400"><?= e(nice_date($p['published_at'])) ?><?= $p['author'] ? ' · ' . e($p['author']) : '' ?></p>
                            <h2 class="mt-2 text-lg font-semibold group-hover:text-brand"><?= e($p['title']) ?></h2>
                            <p class="mt-2 flex-1 text-sm text-slate-600"><?= e($p['excerpt'] ?: '') ?></p>
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand">Read more <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mx-auto max-w-md rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand/10 text-brand"><i class="bi bi-journal-text text-2xl"></i></div>
                <h2 class="mt-4 text-lg font-semibold">No posts yet</h2>
                <p class="mt-2 text-sm text-slate-600">We're working on our first articles. Check back soon.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
