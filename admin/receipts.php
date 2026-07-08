<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/settings.php';
require_admin();
ensure_app_tables();

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);
$errors = [];

/** Build the items array + total from posted parallel arrays. */
function parse_receipt_items(): array
{
    $descs = $_POST['desc'] ?? [];
    $qtys  = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $rates = $_POST['rate'] ?? [];
    $discs = $_POST['disc'] ?? [];
    $items = [];
    $total = 0.0;
    foreach ((array) $descs as $i => $d) {
        $d = trim((string) $d);
        if ($d === '') {
            continue;
        }
        $q    = (float) ($qtys[$i] ?? 0);
        $u    = trim((string) ($units[$i] ?? ''));
        $r    = (float) ($rates[$i] ?? 0);
        $disc = (float) ($discs[$i] ?? 0);
        $amt  = round($q * $r - $disc, 2);
        $total += $amt;
        $items[] = ['desc' => $d, 'qty' => $q, 'unit' => $u, 'rate' => $r, 'disc' => $disc, 'amount' => $amt];
    }
    return [$items, round($total, 2)];
}

// ---------- Handle POST ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo) {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $op = $_POST['op'] ?? '';

        if ($op === 'delete') {
            $pdo->prepare('DELETE FROM receipts WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
            $_SESSION['flash'] = 'Receipt deleted.';
            header('Location: ' . admin_url('receipts'));
            exit;
        }

        if ($op === 'letterhead') {
            try {
                $path = admin_handle_upload('letterhead', 'letterhead');
                if ($path) {
                    $old = setting_get('letterhead_path');
                    setting_set('letterhead_path', $path);
                    if ($old && $old !== $path) {
                        @unlink(__DIR__ . '/../' . $old);
                    }
                    $_SESSION['flash'] = 'Letterhead updated.';
                } else {
                    $_SESSION['flash'] = 'Please choose an image to upload.';
                }
            } catch (RuntimeException $e) {
                $_SESSION['flash'] = $e->getMessage();
            }
            header('Location: ' . admin_url('receipts'));
            exit;
        }

        if ($op === 'letterhead_remove') {
            $old = setting_get('letterhead_path');
            if ($old) {
                @unlink(__DIR__ . '/../' . $old);
            }
            setting_set('letterhead_path', '');
            $_SESSION['flash'] = 'Letterhead removed.';
            header('Location: ' . admin_url('receipts'));
            exit;
        }

        // Create / update.
        $receiptNo   = trim((string) ($_POST['receipt_no'] ?? ''));
        $receiptDate = trim((string) ($_POST['receipt_date'] ?? ''));
        $receivedFrom = trim((string) ($_POST['received_from'] ?? ''));
        $mode        = trim((string) ($_POST['payment_mode'] ?? ''));
        $reference   = trim((string) ($_POST['reference'] ?? ''));
        $notes       = trim((string) ($_POST['notes'] ?? ''));
        $editId      = (int) ($_POST['id'] ?? 0);
        [$items, $total] = parse_receipt_items();

        if ($receiptNo === '') {
            $errors[] = 'Receipt number is required.';
        }
        if ($receiptDate === '' || !strtotime($receiptDate)) {
            $errors[] = 'A valid receipt date is required.';
        }
        if (!$items) {
            $errors[] = 'Add at least one item with a description.';
        }

        if (!$errors) {
            $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
            $date = date('Y-m-d', strtotime($receiptDate));
            if ($editId > 0) {
                $pdo->prepare('UPDATE receipts SET receipt_no=?, receipt_date=?, received_from=?, payment_mode=?, reference=?, items=?, total=?, notes=? WHERE id=?')
                    ->execute([$receiptNo, $date, $receivedFrom, $mode, $reference, $itemsJson, $total, $notes, $editId]);
                $_SESSION['flash'] = 'Receipt updated.';
            } else {
                $pdo->prepare('INSERT INTO receipts (receipt_no, receipt_date, received_from, payment_mode, reference, items, total, notes) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$receiptNo, $date, $receivedFrom, $mode, $reference, $itemsJson, $total, $notes]);
                $editId = (int) $pdo->lastInsertId();
                $_SESSION['flash'] = 'Receipt saved.';
            }
            header('Location: ' . admin_url('receipts'));
            exit;
        }

        // Re-show form with submitted values.
        $action = 'form';
        $receipt = [
            'id' => $editId, 'receipt_no' => $receiptNo, 'receipt_date' => $receiptDate,
            'received_from' => $receivedFrom, 'payment_mode' => $mode, 'reference' => $reference,
            'items' => json_encode($items), 'notes' => $notes, 'total' => $total,
        ];
    }
}

$adminTitle = 'Receipts';
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

// ================= FORM (new / edit) =================
if ($action === 'new' || $action === 'edit' || $action === 'form'):
    if (!isset($receipt)) {
        $receipt = $id > 0 ? db_one('SELECT * FROM receipts WHERE id = ?', [$id]) : null;
        if (!$receipt) {
            $receipt = [
                'id' => 0, 'receipt_no' => '', 'receipt_date' => date('Y-m-d'),
                'received_from' => '', 'payment_mode' => 'Cash', 'reference' => '',
                'items' => '[]', 'notes' => '', 'total' => 0,
            ];
        }
    }
    $items = json_decode($receipt['items'] ?: '[]', true) ?: [];
    if (!$items) {
        $items = [['desc' => '', 'qty' => 1, 'rate' => 0, 'amount' => 0]];
    }
    $dateValue = $receipt['receipt_date'] ? date('Y-m-d', strtotime($receipt['receipt_date'])) : date('Y-m-d');
    ?>
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= admin_url('receipts') ?>" class="text-sm font-semibold text-brand hover:text-brand-dark"><i class="bi bi-arrow-left"></i> Back</a>
        <h2 class="text-xl font-bold"><?= $receipt['id'] ? 'Edit receipt' : 'New receipt' ?></h2>
    </div>

    <form method="POST" class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $receipt['id'] ?>">

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Receipt No. *</label>
                <input type="text" name="receipt_no" required value="<?= htmlspecialchars($receipt['receipt_no']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Receipt Date *</label>
                <input type="date" name="receipt_date" required value="<?= htmlspecialchars($dateValue) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Received with thanks from</label>
                <input type="text" name="received_from" value="<?= htmlspecialchars($receipt['received_from']) ?>" placeholder="Name of payer / organisation" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Payment mode</label>
                <input type="text" name="payment_mode" list="modes" value="<?= htmlspecialchars($receipt['payment_mode']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                <datalist id="modes"><option>Cash</option><option>UPI</option><option>Bank Transfer</option><option>Cheque</option><option>Card</option></datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Reference (txn / cheque no.)</label>
                <input type="text" name="reference" value="<?= htmlspecialchars($receipt['reference']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
        </div>

        <!-- Items -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700">Items *</label>
            <p class="mb-2 text-xs text-slate-400">Amount = Qty × Rate − Discount. Unit is free text (e.g. nos, hrs, kg).</p>
            <div class="mt-2 overflow-x-auto rounded-xl ring-1 ring-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-2 py-2">Description</th>
                            <th class="w-16 px-2 py-2">Qty</th>
                            <th class="w-20 px-2 py-2">Unit</th>
                            <th class="w-24 px-2 py-2">Rate</th>
                            <th class="w-24 px-2 py-2">Discount</th>
                            <th class="w-24 px-2 py-2 text-right">Amount</th>
                            <th class="w-8 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="itemRows">
                        <?php foreach ($items as $it): ?>
                            <tr class="item-row border-t border-slate-100">
                                <td class="px-2 py-2"><input type="text" name="desc[]" value="<?= htmlspecialchars($it['desc'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-brand"></td>
                                <td class="px-2 py-2"><input type="number" step="any" min="0" name="qty[]" value="<?= htmlspecialchars((string) ($it['qty'] ?? 1)) ?>" class="qty w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
                                <td class="px-2 py-2"><input type="text" name="unit[]" value="<?= htmlspecialchars($it['unit'] ?? '') ?>" placeholder="nos" class="w-full rounded-lg border border-slate-200 px-2 py-2 outline-none focus:border-brand"></td>
                                <td class="px-2 py-2"><input type="number" step="any" min="0" name="rate[]" value="<?= htmlspecialchars((string) ($it['rate'] ?? 0)) ?>" class="rate w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
                                <td class="px-2 py-2"><input type="number" step="any" min="0" name="disc[]" value="<?= htmlspecialchars((string) ($it['disc'] ?? 0)) ?>" class="disc w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
                                <td class="px-2 py-2 text-right"><span class="amount font-medium">0.00</span></td>
                                <td class="px-2 py-2 text-center"><button type="button" class="removeRow text-slate-400 hover:text-red-600"><i class="bi bi-x-lg"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50">
                            <td colspan="5" class="px-2 py-2 text-right font-semibold">Total</td>
                            <td class="px-2 py-2 text-right font-bold">₹<span id="grandTotal">0.00</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" id="addRow" class="mt-3 inline-flex items-center gap-2 rounded-full border border-brand px-4 py-2 text-sm font-semibold text-brand transition hover:bg-brand hover:text-white"><i class="bi bi-plus-lg"></i> Add item</button>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700">Notes</label>
            <textarea name="notes" rows="2" maxlength="500" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"><?= htmlspecialchars($receipt['notes']) ?></textarea>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-check-lg"></i> Save receipt</button>
            <?php if ($receipt['id']): ?>
                <a href="<?= admin_url('receipt-print?id=' . (int) $receipt['id']) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-6 py-2.5 font-semibold text-slate-700 transition hover:bg-slate-50"><i class="bi bi-printer"></i> Generate / Print</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Row template -->
    <template id="rowTpl">
        <tr class="item-row border-t border-slate-100">
            <td class="px-2 py-2"><input type="text" name="desc[]" class="w-full rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-brand"></td>
            <td class="px-2 py-2"><input type="number" step="any" min="0" name="qty[]" value="1" class="qty w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
            <td class="px-2 py-2"><input type="text" name="unit[]" placeholder="nos" class="w-full rounded-lg border border-slate-200 px-2 py-2 outline-none focus:border-brand"></td>
            <td class="px-2 py-2"><input type="number" step="any" min="0" name="rate[]" value="0" class="rate w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
            <td class="px-2 py-2"><input type="number" step="any" min="0" name="disc[]" value="0" class="disc w-full rounded-lg border border-slate-200 px-2 py-2 text-right outline-none focus:border-brand"></td>
            <td class="px-2 py-2 text-right"><span class="amount font-medium">0.00</span></td>
            <td class="px-2 py-2 text-center"><button type="button" class="removeRow text-slate-400 hover:text-red-600"><i class="bi bi-x-lg"></i></button></td>
        </tr>
    </template>

    <script>
        (function () {
            var rows = document.getElementById('itemRows');
            var tpl = document.getElementById('rowTpl');
            function recalc() {
                var total = 0;
                rows.querySelectorAll('.item-row').forEach(function (row) {
                    var q = parseFloat(row.querySelector('.qty').value) || 0;
                    var r = parseFloat(row.querySelector('.rate').value) || 0;
                    var d = parseFloat(row.querySelector('.disc').value) || 0;
                    var amt = q * r - d;
                    row.querySelector('.amount').textContent = amt.toFixed(2);
                    total += amt;
                });
                document.getElementById('grandTotal').textContent = total.toFixed(2);
            }
            rows.addEventListener('input', recalc);
            rows.addEventListener('click', function (e) {
                if (e.target.closest('.removeRow')) {
                    if (rows.querySelectorAll('.item-row').length > 1) {
                        e.target.closest('.item-row').remove();
                    } else {
                        e.target.closest('.item-row').querySelectorAll('input').forEach(function (i) { i.value = i.classList.contains('qty') ? '1' : (i.classList.contains('rate') ? '0' : ''); });
                    }
                    recalc();
                }
            });
            document.getElementById('addRow').addEventListener('click', function () {
                rows.appendChild(tpl.content.cloneNode(true));
                recalc();
            });
            recalc();
        })();
    </script>
    <?php
    require __DIR__ . '/footer.php';
    return;
endif;

// ================= LIST =================
$letterhead = setting_get('letterhead_path');
$receipts   = db_all('SELECT id, receipt_no, receipt_date, received_from, total FROM receipts ORDER BY receipt_date DESC, id DESC');
?>
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-xl font-bold">Receipts <span class="text-sm font-normal text-slate-400">(<?= count($receipts) ?>)</span></h2>
    <a href="<?= admin_url('receipts?action=new') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-plus-lg"></i> New receipt</a>
</div>

<!-- Letterhead settings -->
<div class="mb-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h3 class="text-base font-semibold">Letterhead background</h3>
    <p class="mt-1 text-sm text-slate-500">Uploaded image is used as the full-page background when generating receipts. Use a portrait A4 design (JPG/PNG, max 4&nbsp;MB).</p>
    <div class="mt-4 flex flex-wrap items-center gap-4">
        <?php if ($letterhead): ?>
            <img src="/<?= htmlspecialchars($letterhead) ?>" alt="Letterhead" class="h-32 w-auto rounded-lg border border-slate-200 object-contain">
        <?php else: ?>
            <div class="grid h-32 w-24 place-items-center rounded-lg border border-dashed border-slate-300 text-slate-300"><i class="bi bi-file-earmark-image text-2xl"></i></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
            <?= csrf_field() ?>
            <input type="hidden" name="op" value="letterhead">
            <input type="file" name="letterhead" accept="image/*" required class="block text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-brand/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand">
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-upload"></i> Upload</button>
        </form>
        <?php if ($letterhead): ?>
            <form method="POST" onsubmit="return confirm('Remove the letterhead?');">
                <?= csrf_field() ?>
                <input type="hidden" name="op" value="letterhead_remove">
                <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"><i class="bi bi-trash"></i> Remove</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($receipts): ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Receipt No.</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Received from</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($receipts as $r): ?>
                    <tr>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($r['receipt_no']) ?></td>
                        <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars(nice_date($r['receipt_date'])) ?></td>
                        <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($r['received_from'] ?: '—') ?></td>
                        <td class="px-5 py-3 text-right font-semibold"><?= htmlspecialchars(inr((float) $r['total'])) ?></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= admin_url('receipt-print?id=' . $r['id']) ?>" target="_blank" rel="noopener" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-sky2" title="Generate / Print"><i class="bi bi-printer"></i></a>
                                <a href="<?= admin_url('receipts?action=edit&id=' . $r['id']) ?>" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-brand" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" onsubmit="return confirm('Delete this receipt?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="op" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
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
        <p class="text-slate-500">No receipts yet.</p>
        <a href="<?= admin_url('receipts?action=new') ?>" class="mt-4 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Create your first receipt</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
