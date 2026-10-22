<?php
declare(strict_types=1);

/**
 * View Halaman Pelacakan Pesanan Pelanggan.
 * FR-TRK-01, FR-TRK-02, FR-TRK-04, prd.md §6.8, §12.4.
 */
$initialCode = (string) ($code ?? '');
?>

<div class="max-w-xl mx-auto" id="track-page" data-initial-code="<?= e($initialCode) ?>">

  <!-- ── FORM CARI PESANAN (Tampil bila belum ada kode atau user klik cari lain) ── -->
  <div id="track-search-card" class="<?= $initialCode !== '' ? 'hidden' : '' ?> rounded-2xl border border-line bg-white p-6 mb-6">
    <div class="flex items-center gap-2 mb-2">
      <svg class="h-5 w-5 text-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <h1 class="text-xl font-bold tracking-tight text-ink">Lacak Pesanan</h1>
    </div>
    <p class="text-xs text-subtle mb-5">
      Masukkan kode pesanan dan nama pemesan untuk melihat status pesanan Anda.
    </p>

    <form id="form-track-search" class="space-y-4">
      <div>
        <label for="input-search-code" class="block text-xs font-medium text-subtle mb-1.5">
          Kode Pesanan <span class="text-danger">*</span>
        </label>
        <input
          type="text"
          id="input-search-code"
          name="code"
          placeholder="Mis. FE-261022-001"
          autocomplete="off"
          class="h-12 w-full rounded-lg bg-surface px-4 border border-transparent focus:bg-white focus:border-ink focus:outline-none text-sm text-ink placeholder-subtle font-mono uppercase transition-colors"
          value="<?= e($initialCode) ?>"
          required
        >
      </div>

      <div>
        <label for="input-search-name" class="block text-xs font-medium text-subtle mb-1.5">
          Nama Pemesan <span class="text-subtle font-normal">(opsional jika dipesan dari perangkat ini)</span>
        </label>
        <input
          type="text"
          id="input-search-name"
          name="name"
          placeholder="Nama yang digunakan saat checkout"
          autocomplete="name"
          class="h-12 w-full rounded-lg bg-surface px-4 border border-transparent focus:bg-white focus:border-ink focus:outline-none text-sm text-ink placeholder-subtle transition-colors"
        >
      </div>

      <div id="search-error-msg" class="hidden text-xs text-danger"></div>

      <button
        type="submit"
        id="btn-search-submit"
        class="w-full h-12 rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800 transition-colors cursor-pointer flex items-center justify-center gap-2"
      >
        <span>Lacak Sekarang</span>
      </button>
    </form>
  </div>


  <!-- ── PANEL PELACAKAN STATUS (Utama) ── -->
  <div id="track-result-section" class="<?= $initialCode === '' ? 'hidden' : '' ?>">

    <!-- Bar Navigasi Mini: Cari Pesanan Lain -->
    <div class="flex items-center justify-between mb-4">
      <button
        type="button"
        id="btn-toggle-search"
        class="text-xs font-medium text-subtle hover:text-ink transition-colors inline-flex items-center gap-1.5 cursor-pointer"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <span>Lacak pesanan lain</span>
      </button>

      <!-- Indikator Polling Live -->
      <div id="poll-indicator" class="hidden items-center gap-1.5 text-xs text-subtle">
        <span class="inline-block w-2 h-2 rounded-full bg-accent animate-pulse"></span>
        <span id="poll-text">Live updates</span>
        <button
          type="button"
          id="btn-manual-refresh"
          title="Segarkan data sekarang"
          class="ml-1 p-1 rounded hover:bg-surface text-subtle hover:text-ink transition-colors cursor-pointer"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- State Loading Skeleton -->
    <div id="track-loading" class="rounded-2xl border border-line bg-white p-6 space-y-5 animate-pulse">
      <div class="h-7 w-2/3 bg-surface rounded"></div>
      <div class="h-4 w-1/2 bg-surface rounded"></div>
      <div class="h-24 w-full bg-surface rounded-xl"></div>
      <div class="flex gap-2">
        <div class="h-2 flex-1 bg-surface rounded-full"></div>
        <div class="h-2 flex-1 bg-surface rounded-full"></div>
        <div class="h-2 flex-1 bg-surface rounded-full"></div>
        <div class="h-2 flex-1 bg-surface rounded-full"></div>
      </div>
      <div class="h-32 w-full bg-surface rounded-xl"></div>
    </div>

    <!-- State Error Not Found -->
    <div id="track-error" class="hidden rounded-2xl border border-line bg-white p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-surface flex items-center justify-center mx-auto mb-4 text-subtle">
        <svg class="h-8 w-8 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-ink mb-1.5" id="track-error-title">Pesanan tidak ditemukan</h2>
      <p class="text-xs text-subtle max-w-sm mx-auto mb-6" id="track-error-desc">
        Periksa kembali kode pesanan Anda atau pastikan nama pemesan sesuai dengan data saat pemesanan.
      </p>
      <button
        type="button"
        id="btn-retry-search"
        class="h-10 px-6 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
      >
        Cari Ulang
      </button>
    </div>

    <!-- State Konten Detail Pelacakan -->
    <div id="track-content" class="hidden space-y-4">

      <!-- ── Kartu Status Utama ── -->
      <div class="rounded-2xl border border-line bg-white p-6">

        <!-- Judul Besar Dinamis -->
        <h1 id="track-main-title" class="text-2xl sm:text-3xl font-bold tracking-tight text-ink mb-1.5">
          <!-- Diisi JS -->
        </h1>
        <p id="track-subtitle" class="text-xs sm:text-sm text-subtle mb-6">
          <!-- Diisi JS -->
        </p>

        <!-- Kotak Kode Pesanan Besar -->
        <div class="rounded-xl border border-line bg-surface p-4 sm:p-5 flex items-center justify-between mb-6">
          <div>
            <p class="text-[10px] sm:text-xs font-medium text-subtle tracking-wider uppercase">Kode Pesanan</p>
            <p id="track-order-code" class="text-2xl sm:text-3xl font-bold tracking-tight text-ink font-mono tabular-nums">
              —
            </p>
          </div>
          <div id="track-status-badge-container">
            <!-- Badge status dari Fmt.statusBadge -->
          </div>
        </div>

        <!-- ── Progress Bar 4 Segmen Tipis (FR-TRK-01) ── -->
        <div id="track-progress-wrapper" class="space-y-2 mb-2">
          <div class="flex gap-1.5" id="progress-bars">
            <div id="p-bar-1" class="h-1.5 flex-1 rounded-full bg-line transition-colors"></div>
            <div id="p-bar-2" class="h-1.5 flex-1 rounded-full bg-line transition-colors"></div>
            <div id="p-bar-3" class="h-1.5 flex-1 rounded-full bg-line transition-colors"></div>
            <div id="p-bar-4" class="h-1.5 flex-1 rounded-full bg-line transition-colors"></div>
          </div>

          <!-- Label 4 Tahapan -->
          <div class="grid grid-cols-4 text-center text-[11px] font-medium text-subtle pt-1 gap-1">
            <span id="p-label-1" class="truncate">Menunggu bayar</span>
            <span id="p-label-2" class="truncate">Diproses</span>
            <span id="p-label-3" class="truncate">Siap</span>
            <span id="p-label-4" class="truncate">Selesai</span>
          </div>
        </div>

        <!-- Banner Khusus untuk Dibatalkan -->
        <div id="track-cancelled-banner" class="hidden rounded-xl border border-danger-light bg-danger-light/40 p-4">
          <div class="flex items-start gap-2.5">
            <svg class="h-5 w-5 text-danger shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <div>
              <p class="text-xs font-bold text-danger">Pesanan ini telah dibatalkan</p>
              <p id="track-cancel-reason" class="text-xs text-danger/80 mt-0.5">
                Alasan: Tidak ada keterangan
              </p>
            </div>
          </div>
        </div>

      </div><!-- /Kartu Status Utama -->

      <!-- ── Kartu Detail Info Pesanan ── -->
      <div class="rounded-2xl border border-line bg-white p-5 space-y-3">
        <h2 class="text-sm font-bold text-ink">Informasi Pesanan</h2>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div>
            <p class="text-subtle font-medium">Pemesan</p>
            <p id="track-customer-name" class="font-bold text-ink text-sm mt-0.5">—</p>
          </div>
          <div>
            <p class="text-subtle font-medium">Tipe Pesanan</p>
            <p id="track-order-type" class="font-bold text-ink text-sm mt-0.5">—</p>
          </div>
          <div>
            <p class="text-subtle font-medium">Waktu Pemesanan</p>
            <p id="track-created-at" class="font-medium text-ink mt-0.5">—</p>
          </div>
          <div>
            <p class="text-subtle font-medium">Status Pembayaran</p>
            <div id="track-pay-badge" class="mt-0.5">—</div>
          </div>
        </div>

        <!-- Catatan Pemesan -->
        <div id="track-notes-container" class="hidden pt-2 border-t border-line">
          <p class="text-xs text-subtle font-medium">Catatan:</p>
          <p id="track-notes" class="text-xs text-ink mt-0.5 italic"></p>
        </div>
      </div>

      <!-- ── Kartu Rincian Item ── -->
      <div class="rounded-2xl border border-line bg-white p-5">
        <h2 class="text-sm font-bold text-ink mb-3">Rincian Menu</h2>

        <div id="track-items-list" class="divide-y divide-line">
          <!-- Diisi JS -->
        </div>

        <div class="border-t border-line mt-4 pt-4 flex items-center justify-between">
          <span class="text-sm font-bold text-ink">Total Pembayaran</span>
          <span id="track-total-amount" class="text-base font-bold text-ink tabular-nums">Rp 0</span>
        </div>
      </div>

      <!-- Tombol Aksi Tambahan -->
      <div class="flex flex-col sm:flex-row gap-3 pt-2">
        <a
          href="<?= e(url('orders.php')) ?>"
          class="flex-1 h-12 rounded-full bg-surface text-ink text-sm font-medium inline-flex items-center justify-center hover:bg-neutral-200 transition-colors"
        >
          Lihat Riwayat Pesanan
        </a>
        <a
          href="<?= e(url('index.php')) ?>"
          class="flex-1 h-12 rounded-full bg-black text-white text-sm font-medium inline-flex items-center justify-center hover:bg-neutral-800 transition-colors"
        >
          Pesan Menu Lain
        </a>
      </div>

    </div><!-- /track-content -->

  </div><!-- /track-result-section -->

</div><!-- /track-page -->
