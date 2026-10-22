<?php
declare(strict_types=1);

/**
 * View Struk Pembayaran.
 * FR-PAY-05, prd.md §6.11 & §12.4 "Struk".
 *
 * Lebar 80 mm, font monospace, hitam-putih, tanpa navigasi.
 * Tombol Cetak disembunyikan saat @media print.
 *
 * Variabel yang tersedia: $order, $payment, $items, $cafeName
 */

$cafeName   = $cafeName   ?? 'FoodEase';
$order      = $order      ?? [];
$payment    = $payment    ?? [];
$items      = $items      ?? [];

$orderCode  = (string) ($order['order_code']  ?? '—');
$payCode    = (string) ($payment['payment_code'] ?? '—');
$custName   = (string) ($order['customer_name'] ?? '—');
$orderType  = (string) ($order['order_type']  ?? '');
$tableNum   = isset($order['table_number']) && $order['table_number'] !== null
    ? 'Meja ' . (string) $order['table_number']
    : 'Bawa pulang';
$notes      = (string) ($order['notes'] ?? '');
$method     = $payment['method'] === 'cash' ? 'Tunai' : 'QRIS';
$paidAt     = $payment['paid_at']   ?? ($order['updated_at'] ?? date('Y-m-d H:i:s'));
$cashierName = (string) ($payment['cashier_name'] ?? '—');
$total      = (int) ($payment['total']         ?? $order['total'] ?? 0);
$amountPaid = (int) ($payment['amount_paid']   ?? 0);
$change     = (int) ($payment['change_amount'] ?? 0);

$dateFormatted = date('d/m/Y H:i', strtotime($paidAt));
?>
<style>
  @page {
    size: 80mm auto;
    margin: 4mm;
  }

  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 11px;
    color: #000;
    background: #fff;
    max-width: 72mm;
    margin: 0 auto;
    padding: 4mm 0;
    line-height: 1.5;
  }

  .receipt-center {
    text-align: center;
  }

  .receipt-divider {
    border: none;
    border-top: 1px dashed #000;
    margin: 6px 0;
  }

  .receipt-bold {
    font-weight: bold;
  }

  table.items {
    width: 100%;
    border-collapse: collapse;
  }

  table.items td {
    vertical-align: top;
    padding: 1px 0;
  }

  table.items td.item-price {
    text-align: right;
    white-space: nowrap;
    padding-left: 4px;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
  }

  .summary-row.total {
    font-weight: bold;
    font-size: 13px;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #000;
  }

  .btn-print {
    display: block;
    margin: 16px auto 4px;
    width: 100%;
    max-width: 200px;
    padding: 8px 0;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    font-family: inherit;
    letter-spacing: 0.03em;
  }

  .btn-close-tab {
    display: block;
    margin: 6px auto;
    width: 100%;
    max-width: 200px;
    padding: 8px 0;
    background: transparent;
    color: #555;
    border: 1px solid #ccc;
    border-radius: 9999px;
    font-size: 12px;
    cursor: pointer;
    font-family: inherit;
    text-align: center;
    text-decoration: none;
  }

  @media print {
    .no-print {
      display: none !important;
    }
    body {
      padding: 0;
      margin: 0;
    }
  }
</style>

<!-- ─── STRUK ─────────────────────────────────────────────── -->
<div class="receipt-center">
  <p class="receipt-bold" style="font-size:15px;"><?= e($cafeName) ?></p>
  <p style="font-size:10px;">Terima kasih telah berkunjung!</p>
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
        <br><small><?= e(rupiah((int)$item['price'])) ?> × <?= (int) $item['qty'] ?></small>
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
<div class="summary-row" style="margin-top:4px;">
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

<div class="receipt-center" style="margin-top:8px;">
  <p>* * * Terima Kasih * * *</p>
  <p style="font-size:10px; color:#666;">FoodEase — <?= date('Y') ?></p>
</div>

<!-- Tombol Cetak (disembunyikan saat print) -->
<div class="no-print" style="margin-top:16px;">
  <button
    id="btn-print-receipt"
    class="btn-print"
    onclick="window.print()"
  >
    &#128438; Cetak Struk
  </button>
  <button
    class="btn-close-tab"
    onclick="window.close()"
  >
    Tutup Tab
  </button>
</div>
