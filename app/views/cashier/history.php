<?php
declare(strict_types=1);

/**
 * View: Riwayat Transaksi Kasir/Admin (FR-HIS-01..03).
 * Layout: cashier.php. Data dimuat via AJAX oleh cashier-history.js.
 * Variabel tersedia: $role (dari View::render).
 */
$isAdmin = ($role ?? '') === 'admin';
?>
<div class="space-y-6">

  <!-- Filter Bar -->
  <div class="flex flex-wrap gap-3 items-end">

    <!-- Filter: Tanggal Dari -->
    <div class="flex flex-col gap-1">
      <label for="filter-date-from" class="text-xs font-medium text-subtle">Dari</label>
      <input
        type="date"
        id="filter-date-from"
        class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
      >
    </div>

    <!-- Filter: Tanggal Sampai -->
    <div class="flex flex-col gap-1">
      <label for="filter-date-to" class="text-xs font-medium text-subtle">Sampai</label>
      <input
        type="date"
        id="filter-date-to"
        class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
      >
    </div>

    <!-- Filter: Metode -->
    <div class="flex flex-col gap-1">
      <label for="filter-method" class="text-xs font-medium text-subtle">Metode</label>
      <select
        id="filter-method"
        class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
      >
        <option value="">Semua</option>
        <option value="cash">Tunai</option>
        <option value="qris">QRIS</option>
      </select>
    </div>

    <!-- Filter: Status -->
    <div class="flex flex-col gap-1">
      <label for="filter-status" class="text-xs font-medium text-subtle">Status</label>
      <select
        id="filter-status"
        class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
      >
        <option value="">Semua</option>
        <option value="paid">Lunas</option>
        <option value="void">Dibatalkan</option>
      </select>
    </div>

    <!-- Filter: Pencarian -->
    <div class="flex flex-col gap-1 flex-1 min-w-48">
      <label for="filter-q" class="text-xs font-medium text-subtle">Cari</label>
      <input
        type="search"
        id="filter-q"
        placeholder="Kode bayar, kode pesanan, nama..."
        class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
      >
    </div>

    <!-- Tombol Filter -->
    <button
      id="btn-filter"
      class="h-10 px-5 rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800 transition-colors"
    >
      Terapkan
    </button>

    <!-- Tombol Reset -->
    <button
      id="btn-reset"
      class="h-10 px-4 rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors"
    >
      Reset
    </button>
  </div>

  <!-- Tabel Riwayat -->
  <div class="rounded-xl border border-line bg-white overflow-hidden">

    <!-- Loading Skeleton -->
    <div id="history-loading" class="p-8 space-y-3">
      <?php for ($i = 0; $i < 5; $i++): ?>
        <div class="h-10 rounded-lg bg-surface animate-pulse"></div>
      <?php endfor; ?>
    </div>

    <!-- State Error -->
    <div id="history-error" class="hidden p-12 text-center space-y-3">
      <p id="history-error-text" class="text-sm text-subtle">Gagal memuat riwayat transaksi.</p>
      <button
        id="btn-retry"
        class="h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors"
      >
        Coba Lagi
      </button>
    </div>

    <!-- State Kosong -->
    <div id="history-empty" class="hidden p-12 text-center">
      <p class="text-sm text-subtle">Belum ada transaksi yang cocok dengan filter.</p>
    </div>

    <!-- Tabel Data -->
    <div id="history-table-wrap" class="hidden overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-line bg-surface text-xs text-subtle font-medium">
            <th class="h-10 px-4 text-left">Kode Bayar</th>
            <th class="h-10 px-4 text-left">Kode Pesanan</th>
            <th class="h-10 px-4 text-left">Waktu</th>
            <th class="h-10 px-4 text-left">Kasir</th>
            <th class="h-10 px-4 text-left">Metode</th>
            <th class="h-10 px-4 text-right">Total</th>
            <th class="h-10 px-4 text-center">Status</th>
            <?php if ($isAdmin): ?>
              <th class="h-10 px-4 text-center">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="history-tbody"></tbody>
        <tfoot id="history-tfoot" class="hidden">
          <tr class="border-t-2 border-line bg-surface font-bold">
            <td
              colspan="<?= $isAdmin ? 5 : 4 ?>"
              class="h-11 px-4 text-xs font-medium text-subtle"
            >
              Total Lunas (halaman ini)
            </td>
            <td id="history-foot-total" class="h-11 px-4 text-right tabular-nums text-ink"></td>
            <td <?php if ($isAdmin): ?>colspan="2"<?php endif; ?> class="h-11"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Pagination -->
    <div id="history-pagination" class="hidden flex items-center justify-between px-4 py-3 border-t border-line">
      <p id="pagination-info" class="text-xs text-subtle"></p>
      <div class="flex gap-2">
        <button
          id="btn-prev"
          class="h-8 px-3 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          ← Sebelum
        </button>
        <button
          id="btn-next"
          class="h-8 px-3 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Berikut →
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Transaksi -->
<div
  id="detail-modal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
  role="dialog"
  aria-modal="true"
  aria-labelledby="detail-modal-title"
>
  <div class="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-lg overflow-hidden flex flex-col max-h-[90vh]">
    <!-- Header Modal -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-line">
      <h2 id="detail-modal-title" class="text-base font-bold text-ink tracking-tight">Detail Transaksi</h2>
      <button
        id="btn-close-detail"
        class="h-8 w-8 flex items-center justify-center rounded-full bg-surface hover:bg-neutral-200 transition-colors"
        aria-label="Tutup"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Body Modal -->
    <div id="detail-modal-body" class="flex-1 overflow-y-auto px-6 py-4 space-y-4 text-sm">
    </div>

    <!-- Footer Modal -->
    <div id="detail-modal-footer" class="flex gap-2 px-6 py-4 border-t border-line">
      <a
        id="btn-reprint"
        href="#"
        target="_blank"
        class="h-10 flex-1 flex items-center justify-center rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors"
      >
        Cetak Ulang Struk
      </a>
      <?php if ($isAdmin): ?>
        <button
          id="btn-void"
          class="h-10 flex-1 rounded-full bg-danger-light text-danger text-sm font-medium hover:bg-red-100 transition-colors hidden"
        >
          Void Pembayaran
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Void (Admin) -->
<?php if ($isAdmin): ?>
<div
  id="void-modal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
  role="dialog"
  aria-modal="true"
  aria-labelledby="void-modal-title"
>
  <div class="bg-white rounded-2xl w-full max-w-sm mx-4 shadow-lg overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-line">
      <h2 id="void-modal-title" class="text-base font-bold text-ink">Konfirmasi Void</h2>
      <button
        id="btn-close-void"
        class="h-8 w-8 flex items-center justify-center rounded-full bg-surface hover:bg-neutral-200 transition-colors"
        aria-label="Tutup"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="px-6 py-4 space-y-3">
      <p class="text-sm text-subtle">
        Void tidak dapat dibatalkan. Masukkan alasan void:
      </p>
      <div>
        <textarea
          id="void-reason"
          rows="3"
          placeholder="Alasan void (minimal 5 karakter)..."
          class="w-full rounded-lg bg-surface border border-transparent px-3 py-2 text-sm focus:bg-white focus:border-black focus:outline-none resize-none"
        ></textarea>
        <p id="void-reason-error" class="text-xs text-danger mt-1 hidden"></p>
      </div>
    </div>
    <div class="flex gap-2 px-6 pb-5">
      <button
        id="btn-cancel-void"
        class="h-10 flex-1 rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors"
      >
        Batal
      </button>
      <button
        id="btn-confirm-void"
        class="h-10 flex-1 rounded-full bg-danger text-white text-sm font-medium hover:bg-red-700 transition-colors"
      >
        Void
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  /* Data awal yang dibutuhkan cashier-history.js */
  window.HistoryConfig = {
    isAdmin: <?= $isAdmin ? 'true' : 'false' ?>,
    receiptBase: '<?= e(url('cashier/receipt.php?code=')) ?>'
  };
</script>
