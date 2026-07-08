<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/settings.php';
require_admin();
ensure_app_tables();

$id = (int) ($_GET['id'] ?? 0);
$receipt = db_one('SELECT * FROM receipts WHERE id = ?', [$id]);

if (!$receipt) {
    http_response_code(404);
    echo 'Receipt not found.';
    exit;
}

$site       = config('site');
$letterhead = setting_get('letterhead_path');
$items      = json_decode($receipt['items'] ?: '[]', true) ?: [];
$total      = (float) $receipt['total'];
$e          = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$qtyFmt     = static fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');

// Show the discount column only when at least one line carries a discount.
$showDisc = false;
foreach ($items as $it) {
    if ((float) ($it['disc'] ?? 0) > 0) {
        $showDisc = true;
        break;
    }
}
$labelCols = $showDisc ? 6 : 5; // columns before the Amount cell (for the Total row)
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Receipt <?= $e($receipt['receipt_no']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #475569; font-family: "Segoe UI", Roboto, Arial, sans-serif; color: #0f172a; }

        .toolbar { position: sticky; top: 0; display: flex; gap: 12px; justify-content: center; padding: 14px; background: #0f172a; }
        .toolbar button, .toolbar a {
            display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
            border: none; border-radius: 999px; padding: 10px 22px; font-size: 14px; font-weight: 600;
            text-decoration: none;
        }
        .btn-print { background: #f97316; color: #fff; }
        .btn-back { background: rgba(255,255,255,.15); color: #fff; }

        .sheet {
            position: relative; width: 210mm; min-height: 297mm; margin: 20px auto;
            background: #fff; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,.35);
        }
        .letterhead { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }

        /* Content sits above the letterhead, clearing typical header/footer bands. */
        .content { position: relative; z-index: 1; padding: 55mm 20mm 35mm; }

        .doc-title { text-align: center; letter-spacing: 6px; font-size: 20px; font-weight: 700; text-transform: uppercase; margin: 0 0 4px; }
        .doc-rule { width: 70px; height: 3px; background: #f97316; margin: 0 auto 22px; border-radius: 2px; }

        .meta { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 18px; }
        .meta .label { color: #64748b; }
        .rec-from { font-size: 14px; margin-bottom: 18px; }
        .rec-from .label { color: #64748b; }
        .rec-from .val { font-weight: 600; }

        table.items { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 6px; }
        table.items th { background: #f1f5f9; text-align: left; padding: 9px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #475569; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        tfoot td { font-weight: 700; border-top: 2px solid #cbd5e1; border-bottom: none; }

        .words { margin-top: 16px; font-size: 13px; }
        .words .label { color: #64748b; }
        .words .val { font-weight: 600; font-style: italic; }

        .pay { margin-top: 16px; font-size: 13px; color: #334155; }
        .notes { margin-top: 10px; font-size: 12px; color: #64748b; white-space: pre-wrap; }

        .sign { margin-top: 60px; text-align: right; font-size: 13px; }
        .sign .line { display: inline-block; min-width: 220px; border-top: 1px solid #94a3b8; padding-top: 6px; margin-top: 40px; }
        .sign .for { font-weight: 600; }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; width: auto; min-height: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨 Print / Save as PDF</button>
        <a class="btn-back" href="<?= admin_url('receipts') ?>">← Back to receipts</a>
    </div>

    <div class="sheet">
        <?php if ($letterhead): ?>
            <img class="letterhead" src="/<?= $e($letterhead) ?>" alt="">
        <?php endif; ?>
        <div class="content">
            <h1 class="doc-title">Receipt</h1>
            <div class="doc-rule"></div>

            <div class="meta">
                <div><span class="label">Receipt No.:</span> <strong><?= $e($receipt['receipt_no']) ?></strong></div>
                <div><span class="label">Date:</span> <strong><?= $e(date('d M Y', strtotime($receipt['receipt_date']))) ?></strong></div>
            </div>

            <?php if ($receipt['received_from'] !== ''): ?>
                <div class="rec-from"><span class="label">Received with thanks from:</span> <span class="val"><?= $e($receipt['received_from']) ?></span></div>
            <?php endif; ?>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Description</th>
                        <th class="num" style="width:50px">Qty</th>
                        <th style="width:55px">Unit</th>
                        <th class="num" style="width:85px">Rate</th>
                        <?php if ($showDisc): ?><th class="num" style="width:85px">Discount</th><?php endif; ?>
                        <th class="num" style="width:100px">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $it): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $e($it['desc'] ?? '') ?></td>
                            <td class="num"><?= $e($qtyFmt($it['qty'] ?? 0)) ?></td>
                            <td><?= $e($it['unit'] ?? '') ?></td>
                            <td class="num"><?= $e(number_format((float) ($it['rate'] ?? 0), 2)) ?></td>
                            <?php if ($showDisc): ?><td class="num"><?= ((float) ($it['disc'] ?? 0) > 0) ? $e(number_format((float) $it['disc'], 2)) : '—' ?></td><?php endif; ?>
                            <td class="num"><?= $e(number_format((float) ($it['amount'] ?? 0), 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<?= $labelCols ?>" class="num">Total (₹)</td>
                        <td class="num"><?= $e(number_format($total, 2)) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="words"><span class="label">Amount in words:</span> <span class="val"><?= $e(amount_in_words($total)) ?></span></div>

            <div class="pay">
                <?php if ($receipt['payment_mode'] !== ''): ?><span><strong>Mode:</strong> <?= $e($receipt['payment_mode']) ?></span><?php endif; ?>
                <?php if ($receipt['reference'] !== ''): ?><span> &nbsp;·&nbsp; <strong>Ref:</strong> <?= $e($receipt['reference']) ?></span><?php endif; ?>
            </div>
            <?php if ($receipt['notes'] !== ''): ?><div class="notes"><?= nl2br($e($receipt['notes'])) ?></div><?php endif; ?>

            <div class="sign">
                <div class="line">
                    <div class="for">For <?= $e($site['legal_name']) ?></div>
                    <div style="color:#64748b">Authorised Signatory</div>
                </div>
            </div>
        </div>
    </div>

    <script>window.addEventListener('load', function () { /* ready */ });</script>
</body>
</html>
