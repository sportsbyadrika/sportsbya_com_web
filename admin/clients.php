<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);
$errors = [];

// ---------- Handle POST (create / update / delete) ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo) {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $op = $_POST['op'] ?? '';

        if ($op === 'delete') {
            $delId = (int) ($_POST['id'] ?? 0);
            $row   = db_one('SELECT logo_path FROM clients WHERE id = ?', [$delId]);
            $pdo->prepare('DELETE FROM clients WHERE id = ?')->execute([$delId]);
            if (!empty($row['logo_path'])) {
                @unlink(__DIR__ . '/../' . $row['logo_path']);
            }
            $_SESSION['flash'] = 'Client deleted.';
            header('Location: ' . admin_url('clients'));
            exit;
        }

        $name        = trim((string) ($_POST['name'] ?? ''));
        $website     = trim((string) ($_POST['website_url'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;
        $editId      = (int) ($_POST['id'] ?? 0);

        if ($name === '') {
            $errors[] = 'Client name is required.';
        }
        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = 'Website URL is not valid.';
        }

        $logoPath = null;
        if (!$errors) {
            try {
                $logoPath = admin_handle_upload('logo', 'clients');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            if ($editId > 0) {
                $current = db_one('SELECT logo_path FROM clients WHERE id = ?', [$editId]);
                if ($logoPath && !empty($current['logo_path'])) {
                    @unlink(__DIR__ . '/../' . $current['logo_path']);
                }
                $finalLogo = $logoPath ?? ($current['logo_path'] ?? '');
                $pdo->prepare('UPDATE clients SET name=?, logo_path=?, website_url=?, description=?, sort_order=?, is_active=? WHERE id=?')
                    ->execute([$name, $finalLogo, $website, $description, $sortOrder, $isActive, $editId]);
                $_SESSION['flash'] = 'Client updated.';
            } else {
                $pdo->prepare('INSERT INTO clients (name, logo_path, website_url, description, sort_order, is_active) VALUES (?,?,?,?,?,?)')
                    ->execute([$name, $logoPath ?? '', $website, $description, $sortOrder, $isActive]);
                $_SESSION['flash'] = 'Client added.';
            }
            header('Location: ' . admin_url('clients'));
            exit;
        }

        // Re-show the form with submitted values on error.
        $action = 'form';
        $client = [
            'id' => $editId, 'name' => $name, 'website_url' => $website,
            'description' => $description, 'sort_order' => $sortOrder,
            'is_active' => $isActive, 'logo_path' => '',
        ];
    }
}

$adminTitle = 'Our Clients';
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

// ---------- Form view (new / edit) ----------
if ($action === 'new' || $action === 'edit' || $action === 'form'):
    if (!isset($client)) {
        $client = $id > 0
            ? db_one('SELECT * FROM clients WHERE id = ?', [$id])
            : ['id' => 0, 'name' => '', 'website_url' => '', 'description' => '', 'sort_order' => 0, 'is_active' => 1, 'logo_path' => ''];
        if (!$client) { $client = ['id' => 0, 'name' => '', 'website_url' => '', 'description' => '', 'sort_order' => 0, 'is_active' => 1, 'logo_path' => '']; }
    }
    ?>
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= admin_url('clients') ?>" class="text-sm font-semibold text-brand hover:text-brand-dark"><i class="bi bi-arrow-left"></i> Back</a>
        <h2 class="text-xl font-bold"><?= $client['id'] ? 'Edit client' : 'Add client' ?></h2>
    </div>
    <form method="POST" enctype="multipart/form-data" class="max-w-2xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700">Client name *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($client['name']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Website URL</label>
                <input type="url" name="website_url" placeholder="https://example.com" value="<?= htmlspecialchars($client['website_url']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Short description</label>
                <textarea name="description" rows="2" maxlength="500" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"><?= htmlspecialchars($client['description']) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Logo</label>
                <?php if (!empty($client['logo_path'])): ?>
                    <img src="/<?= htmlspecialchars($client['logo_path']) ?>" alt="" class="mt-2 h-16 w-auto rounded-lg border border-slate-200 bg-white p-2 object-contain">
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-brand/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand">
                <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP, GIF or SVG · max 4 MB. Leave empty to keep the current logo.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Sort order</label>
                    <input type="number" name="sort_order" value="<?= (int) $client['sort_order'] ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                </div>
                <label class="mt-7 inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" <?= $client['is_active'] ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand"> Active (show on website)
                </label>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-check-lg"></i> Save client</button>
        </div>
    </form>
    <?php
    require __DIR__ . '/footer.php';
    return;
endif;

// ---------- List view ----------
$clients = db_all('SELECT * FROM clients ORDER BY sort_order ASC, name ASC');
?>
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-xl font-bold">Clients <span class="text-sm font-normal text-slate-400">(<?= count($clients) ?>)</span></h2>
    <a href="<?= admin_url('clients?action=new') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-plus-lg"></i> Add client</a>
</div>

<?php if ($clients): ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Logo</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Website</th>
                    <th class="px-5 py-3">Order</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $c): ?>
                    <tr>
                        <td class="px-5 py-3">
                            <?php if (!empty($c['logo_path'])): ?>
                                <img src="/<?= htmlspecialchars($c['logo_path']) ?>" alt="" class="h-10 w-auto max-w-[80px] object-contain">
                            <?php else: ?>
                                <span class="text-slate-300"><i class="bi bi-image"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($c['name']) ?></td>
                        <td class="px-5 py-3 text-slate-500">
                            <?php if (!empty($c['website_url'])): ?><a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" rel="noopener" class="text-sky2 hover:underline"><?= htmlspecialchars(parse_url($c['website_url'], PHP_URL_HOST) ?: $c['website_url']) ?></a><?php else: ?>—<?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-slate-500"><?= (int) $c['sort_order'] ?></td>
                        <td class="px-5 py-3">
                            <?php if ($c['is_active']): ?>
                                <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= admin_url('clients?action=edit&id=' . $c['id']) ?>" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-brand" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" onsubmit="return confirm('Delete this client?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="op" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
        <p class="text-slate-500">No clients yet.</p>
        <a href="<?= admin_url('clients?action=new') ?>" class="mt-4 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Add your first client</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
