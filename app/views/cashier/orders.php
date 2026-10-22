<?php
declare(strict_types=1);

/**
 * View Halaman Pesanan Masuk Kasir.
 * FR-COR-01..04, prd.md §6.9, §12.3, §12.4.
 */
?>

<div id="cashier-orders-page" class="space-y-6">

  <!-- ── BARIS JUDUL & INDIKATOR REFRESH ── -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Pesanan Masuk</h1>
      <p class="text-xs text-subtle mt-0.5">Kelola antrean dan pantau alur pesanan secara real-time.</p>
    </div>

    <div class="flex items-center gap-3">
      <!-- Banner Notifikasi Pesanan Baru (Discreet, tanpa audio) -->
      <button
        type="button"
        id="btn-new-orders-alert"
        class="hidden h-9 px-3.5 rounded-full bg-accent-light text-accent-dark text-xs font-bold items-center gap-1.5 hover:opacity-90 transition-all cursor-pointer animate-pulse"
      >
        <span class="w-2 h-2 rounded-full bg-accent"></span>
        <span id="new-orders-text">1 pesanan baru</span>
        <span class="underline ml-0.5 font-normal">Muat</span>
      </button>

      <!-- Indikator Auto-Refresh & Tombol Segarkan -->
      <div class="flex items-center gap-2 text-xs text-subtle">
        <span class="inline-block w-2 h-2 rounded-full bg-accent"></span>
        <span id="auto-refresh-status" class="hidden sm:inline">Auto-refresh 8s</span>
        <button
          type="button"
          id="btn-refresh-orders"
          title="Segarkan daftar pesanan"
          class="h-9 px-3 rounded-full bg-surface text-ink hover:bg-neutral-200 transition-colors inline-flex items-center gap-1.5 cursor-pointer"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          <span class="text-xs font-medium">Segarkan</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ── TAB STATUS (FR-COR-01) ── -->
  <div class="flex gap-2 overflow-x-auto pb-1 border-b border-line" id="status-tabs" role="tablist">
    <button
      type="button"
      data-status="pending"
      class="status-tab h-10 px-4 rounded-full text-xs font-bold transition-all bg-black text-white shrink-0 cursor-pointer flex items-center gap-2"
    >
      <span>Belum dibayar</span>
      <span id="tab-count-pending" class="px-2 py-0.5 rounded-full bg-white/20 text-[11px] font-bold">0</span>
    </button>
    <button
      type="button"
      data-status="processing"
      class="status-tab h-10 px-4 rounded-full text-xs font-medium text-subtle hover:text-ink bg-surface transition-all shrink-0 cursor-pointer flex items-center gap-2"
    >
      <span>Diproses</span>
      <span id="tab-count-processing" class="px-2 py-0.5 rounded-full bg-line text-[11px] font-medium text-ink">0</span>
    </button>
    <button
      type="button"
      data-status="ready"
      class="status-tab h-10 px-4 rounded-full text-xs font-medium text-subtle hover:text-ink bg-surface transition-all shrink-0 cursor-pointer flex items-center gap-2"
    >
      <span>Siap</span>
      <span id="tab-count-ready" class="px-2 py-0.5 rounded-full bg-line text-[11px] font-medium text-ink">0</span>
    </button>
    <button
      type="button"
      data-status="completed"
      class="status-tab h-10 px-4 rounded-full text-xs font-medium text-subtle hover:text-ink bg-surface transition-all shrink-0 cursor-pointer flex items-center gap-2"
    >
      <span>Selesai</span>
      <span id="tab-count-completed" class="px-2 py-0.5 rounded-full bg-line text-[11px] font-medium text-ink">0</span>
    </button>
    <button
      type="button"
      data-status="cancelled"
      class="status-tab h-10 px-4 rounded-full text-xs font-medium text-subtle hover:text-ink bg-surface transition-all shrink-0 cursor-pointer flex items-center gap-2"
    >
      <span>Batal</span>
      <span id="tab-count-cancelled" class="px-2 py-0.5 rounded-full bg-line text-[11px] font-medium text-ink">0</span>
    </button>
  </div>

  <!-- ── BARIS FILTER & PENCARIAN (FR-COR-02) ── -->
  <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
    <!-- Kolom Pencarian (Debounce 300ms) -->
    <div class="sm:col-span-5 relative">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </span>
      <input
        type="search"
        id="input-search"
        placeholder="Cari kode pesanan atau nama pemesan..."
        autocomplete="off"
        class="h-10 w-full rounded-lg bg-surface pl-10 pr-4 text-xs sm:text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
      >
    </div>

    <!-- Filter Tipe Pesanan -->
    <div class="sm:col-span-3">
      <select
        id="select-type"
        class="h-10 w-full rounded-lg bg-surface px-3 text-xs sm:text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors appearance-none cursor-pointer"
      >
        <option value="">Semua Tipe</option>
        <option value="dine_in">Makan di tempat</option>
        <option value="takeaway">Bawa pulang</option>
      </select>
    </div>

    <!-- Filter Tanggal -->
    <div class="sm:col-span-4 flex items-center gap-2">
      <input
        type="date"
        id="input-date"
        value="<?= date('Y-m-d') ?>"
        class="h-10 flex-1 rounded-lg bg-surface px-3 text-xs sm:text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors cursor-pointer"
      >
      <button
        type="button"
        id="btn-clear-date"
        title="Tampilkan semua tanggal"
        class="h-10 px-3 rounded-lg bg-surface text-subtle hover:text-ink text-xs font-medium hover:bg-neutral-200 transition-colors shrink-0 cursor-pointer"
      >
        Semua
      </button>
    </div>
  </div>

  <!-- ── TABEL PESANAN ── -->
  <div class="rounded-2xl border border-line bg-white overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs sm:text-sm">
        <thead class="bg-surface/50 border-b border-line text-xs text-subtle font-medium">
          <tr>
            <th class="py-3 px-4 sm:px-6">Kode</th>
            <th class="py-3 px-4">Pelanggan</th>
            <th class="py-3 px-4">Tipe / Meja</th>
            <th class="py-3 px-4 text-right">Total</th>
            <th class="py-3 px-4">Waktu</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 sm:px-6 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="orders-table-body" class="divide-y divide-line">
          <!-- Diisi JS -->
        </tbody>
      </table>
    </div>

    <!-- State Loading Skeleton -->
    <div id="table-loading" class="p-6 space-y-4">
      <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
      <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
      <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
      <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
    </div>

    <!-- State Kosong -->
    <div id="table-empty" class="hidden py-16 text-center">
      <div class="w-14 h-14 rounded-full bg-surface flex items-center justify-center mx-auto mb-3 text-subtle">
        <svg class="h-7 w-7 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <p class="text-sm font-bold text-ink">Tidak ada pesanan</p>
      <p class="text-xs text-subtle mt-0.5">Tidak ditemukan pesanan yang sesuai dengan filter saat ini.</p>
    </div>

    <!-- State Error -->
    <div id="table-error" class="hidden py-12 text-center">
      <p class="text-xs text-danger mb-3" id="table-error-text">Gagal memuat daftar pesanan.</p>
      <button
        type="button"
        id="btn-retry-orders"
        class="h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
      >
        Coba Lagi
      </button>
    </div>

    <!-- Paginasi Bersih -->
    <div id="table-pagination" class="hidden border-t border-line px-4 sm:px-6 py-3 flex items-center justify-between text-xs text-subtle">
      <span id="pagination-info">Menampilkan 0 pesanan</span>
      <div class="flex items-center gap-2">
        <button
          type="button"
          id="btn-prev-page"
          class="h-8 px-3 rounded-lg bg-surface text-ink hover:bg-neutral-200 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
        >
          Sebelumnya
        </button>
        <span id="current-page-text" class="font-medium text-ink">1</span>
        <button
          type="button"
          id="btn-next-page"
          class="h-8 px-3 rounded-lg bg-surface text-ink hover:bg-neutral-200 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
        >
          Berikutnya
        </button>
      </div>
    </div>
  </div>

</div><!-- /cashier-orders-page -->


<!-- ============================================================
     MODAL DETAIL PESANAN KASIR (FR-COR-04)
     ============================================================ -->
<div id="modal-order-detail" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-xl rounded-t-2xl sm:rounded-2xl bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">

      <!-- Garis pegangan mobile (sheet handle) -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto my-3 sm:hidden"></div>

      <!-- Header Modal -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-line shrink-0">
        <div>
          <h2 class="text-base font-bold text-ink flex items-center gap-2">
            <span>Detail Pesanan</span>
            <span id="modal-order-code" class="font-mono text-ink"></span>
          </h2>
          <p id="modal-order-time" class="text-xs text-subtle mt-0.5"></p>
        </div>
        <button
          type="button"
          data-modal-dismiss
          class="p-2 rounded-full hover:bg-surface text-subtle hover:text-ink transition-colors cursor-pointer"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Konten Modal (Scrollable) -->
      <div class="flex-1 overflow-y-auto p-6 space-y-5">

        <!-- Informasi Pelanggan & Status -->
        <div class="rounded-xl border border-line bg-surface p-4 grid grid-cols-2 gap-3 text-xs">
          <div>
            <span class="text-subtle">Nama Pelanggan</span>
            <p id="modal-customer-name" class="font-bold text-ink text-sm mt-0.5">—</p>
          </div>
          <div>
            <span class="text-subtle">Tipe & Meja</span>
            <p id="modal-order-type" class="font-bold text-ink text-sm mt-0.5">—</p>
          </div>
          <div>
            <span class="text-subtle">Status Pesanan</span>
            <div id="modal-status-badge" class="mt-0.5">—</div>
          </div>
          <div>
            <span class="text-subtle">Status Pembayaran</span>
            <div id="modal-pay-badge" class="mt-0.5">—</div>
          </div>
        </div>

        <!-- Catatan Pesanan -->
        <div id="modal-notes-box" class="hidden rounded-xl border border-line p-3 text-xs">
          <p class="font-medium text-subtle">Catatan:</p>
          <p id="modal-notes" class="text-ink mt-0.5 italic"></p>
        </div>

        <!-- Alasan Pembatalan (Jika dibatalkan) -->
        <div id="modal-cancel-box" class="hidden rounded-xl border border-danger-light bg-danger-light/40 p-3 text-xs">
          <p class="font-bold text-danger">Pesanan Dibatalkan</p>
          <p id="modal-cancel-reason" class="text-danger/90 mt-0.5"></p>
        </div>

        <!-- Daftar Item Menu -->
        <div>
          <h3 class="text-xs font-bold text-subtle uppercase tracking-wider mb-2">Item Menu</h3>
          <div id="modal-items-list" class="divide-y divide-line border border-line rounded-xl px-4 py-1">
            <!-- Diisi JS -->
          </div>
        </div>

        <!-- Ringkasan Total -->
        <div class="flex items-center justify-between py-2 border-t border-line">
          <span class="text-sm font-bold text-ink">Total Pembayaran</span>
          <span id="modal-total-amount" class="text-lg font-bold text-ink tabular-nums">Rp 0</span>
        </div>

        <!-- ── Tempat Tombol Aksi (Extensible untuk P12-P13) ── -->
        <div class="pt-2">
          <div class="p-3 bg-surface rounded-xl text-xs text-subtle text-center mb-3">
            Aksi pesanan (Bayar di kasir, Siap, Selesai, Batal) akan diaktifkan pada modul berikutnya (P12–P13).
          </div>
          <div id="modal-action-buttons" class="flex flex-wrap gap-2">
            <!-- Tombol aksi spesifik status akan diisi di sini pada P12/P13 -->
          </div>
        </div>

      </div><!-- /konten modal -->

      <!-- Footer Modal -->
      <div class="px-6 py-3 border-t border-line bg-surface flex justify-end shrink-0">
        <button
          type="button"
          data-modal-dismiss
          class="h-10 px-5 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
        >
          Tutup
        </button>
      </div>

    </div>
  </div>
</div>
