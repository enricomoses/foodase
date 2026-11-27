<?php
declare(strict_types=1);

/**
 * View Struk Pembayaran 80 mm (FR-PAY-05).
 */
$cafeName   = (string) ($cafeName ?? 'FoodEase');
$order      = $order ?? [];
$payment    = $payment ?? [];
$items      = $items ?? [];

$orderCode   = (string) ($order['order_code'] ?? '—');
$payCode     = (string) ($payment['payment_code'] ?? '—');
$custName    = (string) ($order['customer_name'] ?? '—');
$orderType   = (string) ($order['order_type'] ?? '');
$tableNum    = isset($order['table_number']) && $order['table_number'] !== null
    ? 'Meja ' . (string) $order['table_number']
    : 'Bawa pulang';
$notes       = (string) ($order['notes'] ?? '');
$method      = match ($payment['method'] ?? '') {
    'cash'  => 'Tunai',
    'qris'  => 'QRIS',
    default => 'Belum Dibayar',
};
$paidAt      = (string) ($payment['paid_at'] ?? ($order['updated_at'] ?? date('Y-m-d H:i:s')));
$cashierName = (string) ($payment['cashier_name'] ?? '—');
$total       = (int) ($payment['total'] ?? $order['total'] ?? 0);
$amountPaid  = (int) ($payment['amount_paid'] ?? 0);
$change      = (int) ($payment['change_amount'] ?? 0);

$dateFormatted = date('d/m/Y H:i', strtotime($paidAt) ?: time());
?>
<div class="receipt-center">
  <p class="receipt-bold receipt-title"><?= e($cafeName) ?></p>
  <p class="receipt-subtitle">Terima kasih telah berkunjung!</p>
</div>

<hr class="receipt-divider">

<p><strong><?= e($orderCode) ?></strong></p>
<p>Kode Bayar : <?= e($payCode) ?></p>
<p>Tanggal    : <?= e($dateFormatted) ?></p>
<p>Kasir      : <?= e($cashierName) ?></p>
<p>Pelanggan  : <?= e($custName) ?></p>
<p>Tipe       : <?= $orderType === 'dine_in' ? e('Makan di tempat · ' . $tableNum) : e('Bawa pulang') ?></p>
<?php if ($notes !== ''): ?>
<p>Catatan    : <?= e($notes) ?></p>
<?php endif; ?>

<hr class="receipt-divider">

<table class="items">
  <tbody>
    <?php foreach ($items as $item): ?>
    <tr>
      <td>
        <?= e((string) $item['menu_name']) ?>
        <br><small><?= e(rupiah((int) $item['price'])) ?> &times; <?= (int) $item['qty'] ?></small>
      </td>
      <td class="item-price"><?= e(rupiah((int) $item['subtotal'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<hr class="receipt-divider">

<div class="summary-row total">
  <span>TOTAL</span>
  <span><?= e(rupiah($total)) ?></span>
</div>
<div class="summary-row summary-gap">
  <span>Metode</span>
  <span><?= e($method) ?></span>
</div>
<div class="summary-row">
  <span>Dibayar</span>
  <span><?= e(rupiah($amountPaid)) ?></span>
</div>
<div class="summary-row">
  <span>Kembalian</span>
  <span><?= e(rupiah($change)) ?></span>
</div>

<hr class="receipt-divider">

<div class="receipt-center receipt-footer">
  <p>* * * Terima Kasih * * *</p>
  <p class="receipt-brand-foot"><?= e($cafeName) ?> — <?= date('Y') ?></p>
</div>

<div class="no-print receipt-actions">
  <button type="button" id="btn-print-receipt" class="btn-print">
    <?= icon('printer', 'receipt-icon') ?>
    <span>Cetak Struk</span>
  </button>
  <button type="button" id="btn-close-receipt" class="btn-close-tab">
    Tutup Tab
  </button>
</div>
