<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);
$errors = [];

/** Ensure a slug is unique (ignoring the given id). */
function unique_slug(PDO $pdo, string $slug, int $ignoreId = 0): string
{
    $base = $slug;
    $i = 1;
    while (true) {
        $row = db_one('SELECT id FROM blog_posts WHERE slug = ? AND id <> ? LIMIT 1', [$slug, $ignoreId]);
        if (!$row) {
            return $slug;
        }
        $slug = $base . '-' . (++$i);
    }
}

// ---------- Handle POST ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo) {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $op = $_POST['op'] ?? '';

        if ($op === 'delete') {
            $delId = (int) ($_POST['id'] ?? 0);
            $row   = db_one('SELECT cover_path FROM blog_posts WHERE id = ?', [$delId]);
            $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$delId]);
            if (!empty($row['cover_path'])) {
                @unlink(__DIR__ . '/../' . $row['cover_path']);
            }
            $_SESSION['flash'] = 'Post deleted.';
            header('Location: ' . admin_url('blog'));
            exit;
        }

        $title     = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $excerpt   = trim((string) ($_POST['excerpt'] ?? ''));
        $body      = (string) ($_POST['body'] ?? '');
        $author    = trim((string) ($_POST['author'] ?? ''));
        $status    = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $pubInput  = trim((string) ($_POST['published_at'] ?? ''));
        $editId    = (int) ($_POST['id'] ?? 0);

        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if (trim(strip_tags($body)) === '') {
            $errors[] = 'Body content is required.';
        }

        $slug = slugify($slugInput !== '' ? $slugInput : $title);
        $body = sanitize_html($body);
        if ($excerpt === '') {
            $excerpt = excerpt($body, 32);
        }

        // Determine published_at.
        $publishedAt = null;
        if ($status === 'published') {
            $publishedAt = $pubInput !== '' ? date('Y-m-d H:i:s', strtotime($pubInput)) : date('Y-m-d H:i:s');
        } elseif ($pubInput !== '') {
            $publishedAt = date('Y-m-d H:i:s', strtotime($pubInput));
        }

        $coverPath = null;
        if (!$errors) {
            try {
                $coverPath = admin_handle_upload('cover', 'blog');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $slug = unique_slug($pdo, $slug, $editId);
            if ($editId > 0) {
                $current   = db_one('SELECT cover_path FROM blog_posts WHERE id = ?', [$editId]);
                if ($coverPath && !empty($current['cover_path'])) {
                    @unlink(__DIR__ . '/../' . $current['cover_path']);
                }
                $finalCover = $coverPath ?? ($current['cover_path'] ?? '');
                $pdo->prepare('UPDATE blog_posts SET title=?, slug=?, excerpt=?, body=?, cover_path=?, author=?, status=?, published_at=? WHERE id=?')
                    ->execute([$title, $slug, $excerpt, $body, $finalCover, $author, $status, $publishedAt, $editId]);
                $_SESSION['flash'] = 'Post updated.';
            } else {
                $pdo->prepare('INSERT INTO blog_posts (title, slug, excerpt, body, cover_path, author, status, published_at) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$title, $slug, $excerpt, $body, $coverPath ?? '', $author, $status, $publishedAt]);
                $_SESSION['flash'] = 'Post created.';
            }
            header('Location: ' . admin_url('blog'));
            exit;
        }

        $action = 'form';
        $post = [
            'id' => $editId, 'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt,
            'body' => $body, 'cover_path' => '', 'author' => $author,
            'status' => $status, 'published_at' => $pubInput,
        ];
    }
}

$adminTitle = 'Blog';
require __DIR__ . '/header.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($pdo === null):
    echo '<div class="rounded-xl bg-amber-50 p-6 text-amber-800 ring-1 ring-amber-200">Database is not configured. Set your credentials in <code>includes/config.php</code> and run <code>/install.php</code>.</div>';
    require __DIR__ . '/footer.php';
    return;
endif;

if ($flash): ?>
    <div class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200"><?= htmlspecialchars($flash) ?></div>
<?php endif;

if ($errors): ?>
    <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
        <?php foreach ($errors as $err): ?><p><?= htmlspecialchars($err) ?></p><?php endforeach; ?>
    </div>
<?php endif;

// ---------- Form view ----------
if ($action === 'new' || $action === 'edit' || $action === 'form'):
    if (!isset($post)) {
        $post = $id > 0
            ? db_one('SELECT * FROM blog_posts WHERE id = ?', [$id])
            : null;
        if (!$post) {
            $post = ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'cover_path' => '', 'author' => admin_name(), 'status' => 'draft', 'published_at' => ''];
        }
    }
    $pubValue = $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '';
    ?>
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= admin_url('blog') ?>" class="text-sm font-semibold text-brand hover:text-brand-dark"><i class="bi bi-arrow-left"></i> Back</a>
        <h2 class="text-xl font-bold"><?= $post['id'] ? 'Edit post' : 'New post' ?></h2>
    </div>
    <form method="POST" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">

        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <label class="block text-sm font-medium text-slate-700">Title *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($post['title']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">

                <label class="mt-5 block text-sm font-medium text-slate-700">Slug</label>
                <input type="text" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" placeholder="auto-generated from title" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">

                <label class="mt-5 block text-sm font-medium text-slate-700">Excerpt</label>
                <textarea name="excerpt" rows="2" maxlength="500" placeholder="Short summary (auto-generated if left empty)" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"><?= htmlspecialchars($post['excerpt']) ?></textarea>

                <label class="mt-5 block text-sm font-medium text-slate-700">Body *</label>
                <p class="text-xs text-slate-400">Basic HTML is supported: &lt;h2&gt; &lt;h3&gt; &lt;p&gt; &lt;strong&gt; &lt;em&gt; &lt;ul&gt; &lt;ol&gt; &lt;li&gt; &lt;a&gt; &lt;blockquote&gt; &lt;img&gt;.</p>
                <textarea name="body" rows="16" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"><?= htmlspecialchars($post['body']) ?></textarea>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>

                <label class="mt-5 block text-sm font-medium text-slate-700">Publish date</label>
                <input type="datetime-local" name="published_at" value="<?= htmlspecialchars($pubValue) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">

                <label class="mt-5 block text-sm font-medium text-slate-700">Author</label>
                <input type="text" name="author" value="<?= htmlspecialchars($post['author']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <label class="block text-sm font-medium text-slate-700">Cover image</label>
                <?php if (!empty($post['cover_path'])): ?>
                    <img src="/<?= htmlspecialchars($post['cover_path']) ?>" alt="" class="mt-2 aspect-video w-full rounded-lg object-cover">
                <?php endif; ?>
                <input type="file" name="cover" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-brand/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand">
                <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP or GIF · max 4 MB.</p>
            </div>

            <button type="submit" class="w-full rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-check-lg"></i> Save post</button>
        </div>
    </form>
    <?php
    require __DIR__ . '/footer.php';
    return;
endif;

// ---------- List view ----------
$posts = db_all('SELECT id, title, slug, status, author, published_at, updated_at FROM blog_posts ORDER BY COALESCE(published_at, updated_at) DESC, id DESC');
?>
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-xl font-bold">Blog posts <span class="text-sm font-normal text-slate-400">(<?= count($posts) ?>)</span></h2>
    <a href="<?= admin_url('blog?action=new') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-plus-lg"></i> New post</a>
</div>

<?php if ($posts): ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($posts as $p): ?>
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-medium"><?= htmlspecialchars($p['title']) ?></p>
                            <p class="text-xs text-slate-400">/blog/<?= htmlspecialchars($p['slug']) ?></p>
                        </td>
                        <td class="px-5 py-3">
                            <?php if ($p['status'] === 'published'): ?>
                                <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">Published</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars(nice_date($p['published_at']) ?: nice_date($p['updated_at'])) ?></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <?php if ($p['status'] === 'published'): ?>
                                    <a href="/blog/<?= htmlspecialchars($p['slug']) ?>" target="_blank" rel="noopener" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-sky2" title="View"><i class="bi bi-box-arrow-up-right"></i></a>
                                <?php endif; ?>
                                <a href="<?= admin_url('blog?action=edit&id=' . $p['id']) ?>" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-brand" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" onsubmit="return confirm('Delete this post?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="op" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
        <p class="text-slate-500">No posts yet.</p>
        <a href="<?= admin_url('blog?action=new') ?>" class="mt-4 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Write your first post</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
