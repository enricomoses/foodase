<?php
declare(strict_types=1);
?>
<div class="receipt-center receipt-error">
  <p class="receipt-bold">Struk Tidak Tersedia</p>
  <p><?= e($message ?? 'Data pesanan atau pembayaran tidak ditemukan.') ?></p>
  <div class="no-print receipt-actions">
    <button type="button" id="btn-close-receipt" class="btn-close-tab">Tutup Tab</button>
  </div>
</div>
