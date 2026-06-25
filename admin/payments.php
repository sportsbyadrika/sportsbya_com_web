<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/settings.php';
require_admin();
ensure_app_tables();

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);
$errors = [];

// ---------- Handle POST ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo) {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $op = $_POST['op'] ?? '';

        if ($op === 'delete') {
            $pdo->prepare('DELETE FROM payments WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
            $_SESSION['flash'] = 'Payment deleted.';
            header('Location: ' . admin_url('payments'));
            exit;
        }

        $date        = trim((string) ($_POST['payment_date'] ?? ''));
        $paidTo      = trim((string) ($_POST['paid_to'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $mode        = trim((string) ($_POST['payment_mode'] ?? ''));
        $reference   = trim((string) ($_POST['reference'] ?? ''));
        $amount      = (float) ($_POST['amount'] ?? 0);
        $notes       = trim((string) ($_POST['notes'] ?? ''));
        $editId      = (int) ($_POST['id'] ?? 0);

        if ($date === '' || !strtotime($date)) {
            $errors[] = 'A valid payment date is required.';
        }
        if ($paidTo === '') {
            $errors[] = 'Paid to is required.';
        }
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than zero.';
        }

        if (!$errors) {
            $d = date('Y-m-d', strtotime($date));
            if ($editId > 0) {
                $pdo->prepare('UPDATE payments SET payment_date=?, paid_to=?, description=?, payment_mode=?, reference=?, amount=?, notes=? WHERE id=?')
                    ->execute([$d, $paidTo, $description, $mode, $reference, $amount, $notes, $editId]);
                $_SESSION['flash'] = 'Payment updated.';
            } else {
                $pdo->prepare('INSERT INTO payments (payment_date, paid_to, description, payment_mode, reference, amount, notes) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$d, $paidTo, $description, $mode, $reference, $amount, $notes]);
                $_SESSION['flash'] = 'Payment recorded.';
            }
            header('Location: ' . admin_url('payments'));
            exit;
        }

        $action = 'form';
        $payment = [
            'id' => $editId, 'payment_date' => $date, 'paid_to' => $paidTo,
            'description' => $description, 'payment_mode' => $mode, 'reference' => $reference,
            'amount' => $amount, 'notes' => $notes,
        ];
    }
}

$adminTitle = 'Payments';
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

// ================= FORM =================
if ($action === 'new' || $action === 'edit' || $action === 'form'):
    if (!isset($payment)) {
        $payment = $id > 0 ? db_one('SELECT * FROM payments WHERE id = ?', [$id]) : null;
        if (!$payment) {
            $payment = ['id' => 0, 'payment_date' => date('Y-m-d'), 'paid_to' => '', 'description' => '', 'payment_mode' => 'Cash', 'reference' => '', 'amount' => '', 'notes' => ''];
        }
    }
    $dateValue = $payment['payment_date'] ? date('Y-m-d', strtotime($payment['payment_date'])) : date('Y-m-d');
    ?>
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= admin_url('payments') ?>" class="text-sm font-semibold text-brand hover:text-brand-dark"><i class="bi bi-arrow-left"></i> Back</a>
        <h2 class="text-xl font-bold"><?= $payment['id'] ? 'Edit payment' : 'Record payment' ?></h2>
    </div>
    <form method="POST" class="max-w-2xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $payment['id'] ?>">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Date *</label>
                <input type="date" name="payment_date" required value="<?= htmlspecialchars($dateValue) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Amount (₹) *</label>
                <input type="number" step="any" min="0" name="amount" required value="<?= htmlspecialchars((string) $payment['amount']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Paid to *</label>
                <input type="text" name="paid_to" required value="<?= htmlspecialchars($payment['paid_to']) ?>" placeholder="Vendor / person / purpose" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Description / towards</label>
                <input type="text" name="description" value="<?= htmlspecialchars($payment['description']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Payment mode</label>
                <input type="text" name="payment_mode" list="modes" value="<?= htmlspecialchars($payment['payment_mode']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                <datalist id="modes"><option>Cash</option><option>UPI</option><option>Bank Transfer</option><option>Cheque</option><option>Card</option></datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Reference (txn / cheque no.)</label>
                <input type="text" name="reference" value="<?= htmlspecialchars($payment['reference']) ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Notes</label>
                <textarea name="notes" rows="2" maxlength="500" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"><?= htmlspecialchars($payment['notes']) ?></textarea>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-check-lg"></i> Save payment</button>
        </div>
    </form>
    <?php
    require __DIR__ . '/footer.php';
    return;
endif;

// ================= LIST =================
$payments = db_all('SELECT * FROM payments ORDER BY payment_date DESC, id DESC');
$grand = 0.0;
foreach ($payments as $p) { $grand += (float) $p['amount']; }
?>
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-xl font-bold">Payments <span class="text-sm font-normal text-slate-400">(<?= count($payments) ?>)</span></h2>
    <a href="<?= admin_url('payments?action=new') ?>" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"><i class="bi bi-plus-lg"></i> Record payment</a>
</div>

<?php if ($payments): ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Paid to</th>
                    <th class="px-5 py-3">Towards</th>
                    <th class="px-5 py-3">Mode</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars(nice_date($p['payment_date'])) ?></td>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($p['paid_to']) ?></td>
                        <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($p['description'] ?: '—') ?></td>
                        <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars($p['payment_mode'] ?: '—') ?></td>
                        <td class="px-5 py-3 text-right font-semibold"><?= htmlspecialchars(inr((float) $p['amount'])) ?></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= admin_url('payments?action=edit&id=' . $p['id']) ?>" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-brand" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" onsubmit="return confirm('Delete this payment?');">
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
            <tfoot>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="4" class="px-5 py-3 text-right">Total</td>
                    <td class="px-5 py-3 text-right"><?= htmlspecialchars(inr($grand)) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
        <p class="text-slate-500">No payments recorded yet.</p>
        <a href="<?= admin_url('payments?action=new') ?>" class="mt-4 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Record your first payment</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
