<?php
declare(strict_types=1);

/**
 * View Halaman Pesanan Masuk Kasir — POS Terminal Workspace.
 * FR-COR-01..04, FR-PAY-01..04, prd.md §6.9, §6.11, §12.3, §12.4.
 */
?>

<div id="cashier-orders-page" class="space-y-4" data-user-role="<?= e(\App\Core\Auth::role() ?? 'cashier') ?>">

  <!-- ── BARIS JUDUL & INDIKATOR REFRESH ── -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Pesanan Masuk</h1>
      <p class="text-xs text-subtle mt-0.5">Kelola antrean kasir dan proses pembayaran secara instan.</p>
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

  <!-- ── POS WORKSPACE (DUAL-PANE: ANTREAN + TERMINAL CEPAT) ── -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

    <!-- ── KOLOM KIRI: DAFTAR ANTREAN PESANAN (lg:col-span-7) ── -->
    <div class="lg:col-span-7 space-y-3">
      <div class="rounded-2xl border border-line bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-surface/50 border-b border-line text-xs text-subtle font-medium">
              <tr>
                <th class="py-3 px-3.5 sm:px-4">Kode</th>
                <th class="py-3 px-3">Pelanggan</th>
                <th class="py-3 px-3 text-right">Total</th>
                <th class="py-3 px-3">Waktu</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3 sm:px-4 text-right">Aksi Cepat</th>
              </tr>
            </thead>
            <tbody id="orders-table-body" class="divide-y divide-line">
              <!-- Diisi JS -->
            </tbody>
          </table>
        </div>

        <!-- State Loading Skeleton -->
        <div id="table-loading" class="p-6 space-y-3">
          <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
          <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
          <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
          <div class="h-10 w-full bg-surface rounded animate-pulse"></div>
        </div>

        <!-- State Kosong -->
        <div id="table-empty" class="hidden py-14 text-center">
          <div class="w-12 h-12 rounded-full bg-surface flex items-center justify-center mx-auto mb-3 text-subtle">
            <svg class="h-6 w-6 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <p class="text-sm font-bold text-ink">Tidak ada pesanan</p>
          <p class="text-xs text-subtle mt-0.5">Tidak ditemukan pesanan pada status/filter ini.</p>
        </div>

        <!-- State Error -->
        <div id="table-error" class="hidden py-10 text-center">
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
        <div id="table-pagination" class="hidden border-t border-line px-4 py-2.5 flex items-center justify-between text-xs text-subtle">
          <span id="pagination-info">Menampilkan 0 pesanan</span>
          <div class="flex items-center gap-2">
            <button
              type="button"
              id="btn-prev-page"
              class="h-7 px-2.5 rounded-lg bg-surface text-ink hover:bg-neutral-200 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
            >
              Sebelumnya
            </button>
            <span id="current-page-text" class="font-medium text-ink">1</span>
            <button
              type="button"
              id="btn-next-page"
              class="h-7 px-2.5 rounded-lg bg-surface text-ink hover:bg-neutral-200 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer"
            >
              Berikutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── KOLOM KANAN: POS TERMINAL & DIRECT PAYMENT (lg:col-span-5) ── -->
    <div id="pos-terminal-wrapper" class="lg:col-span-5 lg:sticky lg:top-4 z-10">

      <!-- Mobile Backdrop Drawer -->
      <div id="pos-terminal-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

      <!-- State Kosong / Belum Ada Pesanan Terpilih -->
      <div id="pos-terminal-empty" class="rounded-2xl border border-dashed border-line bg-surface/60 p-8 text-center flex flex-col items-center justify-center min-h-[360px]">
        <div class="w-12 h-12 rounded-full bg-white border border-line flex items-center justify-center text-subtle mb-3">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-ink">Pilih Pesanan</p>
        <p class="text-xs text-subtle mt-1 max-w-xs">
          Klik baris pesanan di sebelah kiri untuk melihat rincian item dan memproses pembayaran langsung.
        </p>
      </div>

      <!-- Kartu Terminal Pesanan & Pembayaran Langsung -->
      <div id="pos-terminal-panel" class="hidden rounded-2xl border border-line bg-white shadow-lg overflow-hidden flex flex-col transition-all">

        <!-- Header Panel -->
        <div class="px-5 py-3.5 border-b border-line bg-surface/50 flex items-center justify-between shrink-0">
          <div>
            <div class="flex items-center gap-2">
              <span id="terminal-order-code" class="font-mono text-sm font-bold text-ink tracking-tight"></span>
              <span id="terminal-order-type-badge"></span>
            </div>
            <p id="terminal-order-time" class="text-[11px] text-subtle mt-0.5"></p>
          </div>
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              id="btn-terminal-close"
              title="Tutup rincian"
              class="p-1.5 rounded-full hover:bg-neutral-200 text-subtle hover:text-ink transition-colors cursor-pointer"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Scrollable Body Terminal -->
        <div class="p-5 space-y-4 max-h-[calc(100vh-220px)] overflow-y-auto">

          <!-- Ringkasan Pelanggan & Status -->
          <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-surface text-xs border border-line">
            <div>
              <span class="text-[11px] text-subtle">Nama Pelanggan</span>
              <p id="terminal-customer-name" class="font-bold text-ink text-xs mt-0.5 truncate">—</p>
            </div>
            <div>
              <span class="text-[11px] text-subtle">Status Bayar</span>
              <div id="terminal-payment-badge" class="mt-0.5"></div>
            </div>
          </div>

          <!-- Catatan Pesanan (Jika Ada) -->
          <div id="terminal-notes-box" class="hidden rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-950/30 p-2.5 text-xs text-amber-900 dark:text-amber-200">
            <div class="flex items-start gap-1.5">
              <svg class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              <div>
                <span class="font-bold text-[11px] uppercase tracking-wide">Catatan Khusus:</span>
                <p id="terminal-notes" class="mt-0.5 font-medium"></p>
              </div>
            </div>
          </div>

          <!-- Daftar Rincian Item Menu -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-[11px] font-bold text-subtle uppercase tracking-wider">Item Pesanan</span>
              <span id="terminal-items-count" class="text-[11px] text-subtle">0 item</span>
            </div>
            <div id="terminal-items-list" class="divide-y divide-line border border-line rounded-xl px-3 py-1 bg-surface/30 max-h-48 overflow-y-auto">
              <!-- Diisi JS -->
            </div>
          </div>

          <!-- Grand Total Pembayaran -->
          <div class="flex items-center justify-between py-2 border-t border-b border-line">
            <span class="text-xs font-bold text-subtle">Total Pembayaran</span>
            <span id="terminal-total-amount" class="text-xl font-bold text-ink tabular-nums tracking-tight">Rp 0</span>
          </div>

          <!-- ── TERMINAL AKSI / PEMBAYARAN LANGSUNG ── -->
          <div id="terminal-action-container">

            <!-- 1. MODE BAYAR (Jika status 'pending' / belum bayar) -->
            <div id="terminal-pay-mode" class="space-y-3.5 hidden">
              <!-- Segmented Control: Tunai / QRIS -->
              <div class="grid grid-cols-2 p-1 rounded-xl bg-surface border border-line text-xs font-bold">
                <button
                  type="button"
                  id="terminal-method-cash"
                  class="h-8 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer bg-black text-white shadow-sm"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/>
                  </svg>
                  <span>Tunai (Cash)</span>
                </button>
                <button
                  type="button"
                  id="terminal-method-qris"
                  class="h-8 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-subtle hover:text-ink"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                  </svg>
                  <span>QRIS</span>
                </button>
              </div>

              <!-- Bagian Pembayaran Tunai -->
              <div id="terminal-cash-section" class="space-y-3">
                <!-- Tombol Nominal Cepat -->
                <div>
                  <label class="block text-[11px] font-medium text-subtle mb-1.5">Pilih Nominal Cepat</label>
                  <div id="terminal-quick-amounts" class="flex flex-wrap gap-1.5">
                    <!-- Diisi JS -->
                  </div>
                </div>

                <!-- Input Nominal Diterima -->
                <div>
                  <label for="terminal-cash-input" class="block text-[11px] font-medium text-subtle mb-1">
                    Uang Diterima (Rp)
                  </label>
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-subtle pointer-events-none">Rp</span>
                    <input
                      type="text"
                      id="terminal-cash-input"
                      inputmode="numeric"
                      autocomplete="off"
                      placeholder="0"
                      class="h-11 w-full rounded-xl bg-surface pl-10 pr-4 text-base font-bold text-ink tabular-nums border border-line focus:bg-white focus:border-ink focus:outline-none transition-colors"
                    >
                  </div>
                </div>

                <!-- Indikator Kembalian Live -->
                <div id="terminal-change-box" class="p-2.5 rounded-xl bg-surface border border-line flex items-center justify-between text-xs">
                  <span class="text-subtle font-medium" id="terminal-change-label">Kembalian:</span>
                  <span id="terminal-change-amount" class="text-sm font-bold text-ink tabular-nums">Rp 0</span>
                </div>

                <!-- Tombol Konfirmasi Bayar Tunai (Warna Hijau Aksen) -->
                <button
                  type="button"
                  id="btn-terminal-submit-cash"
                  class="w-full h-11 rounded-full text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span>Konfirmasi Pembayaran Tunai (Enter)</span>
                </button>
              </div>

              <!-- Bagian QRIS -->
              <div id="terminal-qris-section" class="hidden space-y-3 text-center">
                <div class="p-4 rounded-xl border border-line bg-surface/50 flex flex-col items-center justify-center">
                  <!-- QR Mockup SVG -->
                  <div class="qr-mockup w-36 h-36 bg-white p-2 rounded-xl border border-line flex items-center justify-center shadow-sm">
                    <svg class="w-full h-full text-ink" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm2 2h2v2h-2v-2zm2 2h2v4h-2v-4zm-4 2h2v2h-2v-2zm-2-2h2v2h-2v-2zm8 0h2v2h-2v-2z"/>
                    </svg>
                  </div>
                  <p class="text-xs font-bold text-ink mt-2">Scan QRIS Kafe</p>
                  <p class="text-[11px] text-subtle mt-0.5">Tunjukkan kode QR kepada pelanggan untuk scan via e-wallet/mobile banking.</p>
                </div>

                <button
                  type="button"
                  id="btn-terminal-submit-qris"
                  class="w-full h-11 rounded-full text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm hover:opacity-90"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span>Konfirmasi Pembayaran QRIS</span>
                </button>
              </div>

              <!-- Batalkan Pesanan (Tombol Merah Tegas) -->
              <div class="pt-2">
                <button
                  type="button"
                  id="btn-terminal-open-cancel"
                  class="w-full h-10 rounded-full text-white text-xs font-bold hover:bg-red-700 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                  <span>Batalkan Pesanan</span>
                </button>
              </div>
            </div>

            <!-- 2. MODE STATUS: DIPROSES -->
            <div id="terminal-processing-mode" class="space-y-2.5 hidden">
              <div class="p-3 rounded-xl bg-surface border border-line text-xs text-subtle text-center">
                Pesanan sudah dibayar dan sedang disiapkan di dapur.
              </div>
              <button
                type="button"
                id="btn-terminal-mark-ready"
                class="w-full h-11 rounded-full bg-accent hover:opacity-90 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Tandai Siap Saji</span>
              </button>
              <div class="flex gap-2">
                <a
                  id="btn-terminal-print-receipt-proc"
                  href="#"
                  target="_blank"
                  class="flex-1 h-10 rounded-full bg-surface hover:bg-neutral-200 text-ink text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 border border-line"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                  </svg>
                  <span>Cetak Struk</span>
                </a>
                <button
                  type="button"
                  id="btn-terminal-cancel-admin-proc"
                  class="hidden h-10 px-4 rounded-full bg-surface text-danger hover:bg-neutral-200 text-xs font-medium cursor-pointer"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- 3. MODE STATUS: SIAP SAJI -->
            <div id="terminal-ready-mode" class="space-y-2.5 hidden">
              <div class="p-3 rounded-xl bg-surface border border-line text-xs text-subtle text-center">
                Pesanan siap disajikan ke meja atau diambil oleh pelanggan.
              </div>
              <button
                type="button"
                id="btn-terminal-mark-completed"
                class="w-full h-11 rounded-full bg-accent hover:opacity-90 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 13l4 4L19 7"/>
                </svg>
                <span>Tandai Selesai</span>
              </button>
              <a
                id="btn-terminal-print-receipt-ready"
                href="#"
                target="_blank"
                class="w-full h-10 rounded-full bg-surface hover:bg-neutral-200 text-ink text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 border border-line"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                </svg>
                <span>Cetak Struk</span>
              </a>
            </div>

            <!-- 4. MODE STATUS: SELESAI -->
            <div id="terminal-completed-mode" class="space-y-2.5 hidden">
              <div class="p-3 rounded-xl bg-accent-light/50 border border-accent/20 text-xs text-accent-dark text-center font-medium">
                ✓ Pesanan telah selesai dan lunas.
              </div>
              <a
                id="btn-terminal-print-receipt-done"
                href="#"
                target="_blank"
                class="w-full h-11 rounded-full bg-surface hover:bg-neutral-200 text-ink text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 border border-line"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                </svg>
                <span>Cetak Ulang Struk</span>
              </a>
            </div>

            <!-- 5. MODE STATUS: DIBATALKAN -->
            <div id="terminal-cancelled-mode" class="hidden p-3 rounded-xl border border-danger-light bg-danger-light/30 text-xs text-danger">
              <p class="font-bold">Pesanan Dibatalkan</p>
              <p id="terminal-cancel-reason" class="mt-0.5"></p>
            </div>

          </div><!-- /terminal-action-container -->

        </div><!-- /scrollable body -->

      </div><!-- /pos-terminal-panel -->

    </div><!-- /lg:col-span-5 -->

  </div><!-- /grid -->

</div><!-- /cashier-orders-page -->


<!-- ============================================================
     MODAL KONFIRMASI PEMBATALAN PESANAN (FR-STS-02, FR-STS-03)
     ============================================================ -->
<div id="modal-cancel-order" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-dismiss></div>

  <div class="flex min-h-full items-center justify-center p-4">
    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl overflow-hidden p-6 space-y-4">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="text-base font-bold text-ink flex items-center gap-2">
            <span>Batalkan Pesanan</span>
            <span id="cancel-modal-order-code" class="font-mono text-danger"></span>
          </h3>
          <p id="cancel-modal-description" class="text-xs text-subtle mt-1"></p>
        </div>
        <button type="button" data-modal-dismiss class="p-1 rounded-full text-subtle hover:text-ink cursor-pointer">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Warning Box jika Paid (Void) -->
      <div id="cancel-modal-void-warning" class="hidden rounded-xl border border-danger-light bg-danger-light/30 p-3 text-xs text-danger">
        <p class="font-bold">Perhatian: Pembayaran akan di-void!</p>
        <p class="mt-0.5 text-danger/90">Pesanan ini sudah dibayar. Pembayaran akan dibatalkan/void dan pesanan tidak dapat diproses lagi.</p>
      </div>

      <!-- Input Alasan -->
      <div id="cancel-reason-container" class="space-y-1.5">
        <label for="cancel-reason-input" class="block text-xs font-medium text-ink">
          Alasan Pembatalan <span id="cancel-reason-required-badge" class="text-danger font-bold">*</span>
        </label>
        <textarea
          id="cancel-reason-input"
          rows="3"
          placeholder="Tulis alasan pembatalan..."
          class="w-full rounded-lg bg-surface p-3 text-xs text-ink placeholder-subtle border border-line focus:bg-white focus:border-ink focus:outline-none transition-colors resize-none"
        ></textarea>
        <p id="cancel-reason-error" class="hidden text-[11px] text-danger"></p>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
        <button
          type="button"
          data-modal-dismiss
          class="h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
        >
          Kembali
        </button>
        <button
          type="button"
          id="btn-confirm-cancel-submit"
          class="h-9 px-4 rounded-full bg-danger text-white text-xs font-bold hover:bg-red-700 transition-colors inline-flex items-center gap-1.5 cursor-pointer"
        >
          Konfirmasi Batalkan
        </button>
      </div>
    </div>
  </div>
</div>
