<?php
$adminTitle = 'Dashboard';
require __DIR__ . '/header.php';

$clientCount    = (int) (db_one('SELECT COUNT(*) AS c FROM clients')['c'] ?? 0);
$postCount      = (int) (db_one("SELECT COUNT(*) AS c FROM blog_posts")['c'] ?? 0);
$publishedCount = (int) (db_one("SELECT COUNT(*) AS c FROM blog_posts WHERE status='published'")['c'] ?? 0);
$messageCount   = (int) (db_one('SELECT COUNT(*) AS c FROM contact_messages')['c'] ?? 0);
$recentMessages = db_all('SELECT name, email, mobile, message, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5');

$cards = [
    ['Clients', $clientCount, 'bi-people', admin_url('clients')],
    ['Blog posts', $postCount, 'bi-journal-text', admin_url('blog')],
    ['Published', $publishedCount, 'bi-check-circle', admin_url('blog')],
    ['Enquiries', $messageCount, 'bi-envelope', '#'],
];
?>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ($cards as [$label, $value, $icon, $href]): ?>
        <a href="<?= htmlspecialchars($href) ?>" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand/10 text-brand"><i class="bi <?= $icon ?> text-lg"></i></span>
                <span class="text-3xl font-extrabold"><?= $value ?></span>
            </div>
            <p class="mt-3 text-sm font-medium text-slate-500"><?= $label ?></p>
        </a>
    <?php endforeach; ?>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-base font-semibold">Quick actions</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="<?= admin_url('clients?action=new') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-plus-lg"></i> Add client</a>
            <a href="<?= admin_url('blog?action=new') ?>" class="inline-flex items-center gap-2 rounded-full border border-brand px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand hover:text-white"><i class="bi bi-plus-lg"></i> New blog post</a>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-base font-semibold">Recent enquiries</h2>
        <?php if ($recentMessages): ?>
            <ul class="mt-4 divide-y divide-slate-100 text-sm">
                <?php foreach ($recentMessages as $m): ?>
                    <li class="py-3">
                        <div class="flex items-center justify-between">
                            <span class="font-medium"><?= htmlspecialchars($m['name']) ?></span>
                            <span class="text-xs text-slate-400"><?= htmlspecialchars(nice_date($m['created_at'])) ?></span>
                        </div>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($m['mobile']) ?> · <?= htmlspecialchars($m['email']) ?></p>
                        <p class="mt-1 text-slate-600"><?= htmlspecialchars(excerpt($m['message'], 18)) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="mt-4 text-sm text-slate-500">No enquiries yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
