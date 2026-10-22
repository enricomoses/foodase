<?php
declare(strict_types=1);

/**
 * View Halaman Riwayat Pesanan Pelanggan (Session).
 * FR-TRK-03, prd.md §6.8, §12.3, §12.4.
 */
?>

<div class="max-w-2xl mx-auto" id="history-page">

  <!-- Header Halaman -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Riwayat Pesanan</h1>
      <p class="text-xs text-subtle mt-0.5">Daftar pesanan Anda yang dibuat dari perangkat ini.</p>
    </div>

    <!-- Tautan Lacak Pesanan Lain -->
    <a
      href="<?= e(url('track.php')) ?>"
      class="inline-flex items-center gap-1.5 text-xs font-medium text-ink hover:text-subtle transition-colors shrink-0"
    >
      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <span>Lacak dengan kode & nama &rarr;</span>
    </a>
  </div>

  <!-- State Loading (Skeleton) -->
  <div id="history-loading" class="space-y-4">
    <div class="rounded-2xl border border-line bg-white p-5 space-y-3 animate-pulse">
      <div class="flex items-center justify-between">
        <div class="h-5 w-36 bg-surface rounded"></div>
        <div class="h-5 w-20 bg-surface rounded-full"></div>
      </div>
      <div class="h-4 w-48 bg-surface rounded"></div>
      <div class="h-10 w-full bg-surface rounded-xl"></div>
      <div class="flex items-center justify-between pt-2 border-t border-line">
        <div class="h-5 w-28 bg-surface rounded"></div>
        <div class="h-8 w-28 bg-surface rounded-full"></div>
      </div>
    </div>
    <div class="rounded-2xl border border-line bg-white p-5 space-y-3 animate-pulse">
      <div class="flex items-center justify-between">
        <div class="h-5 w-36 bg-surface rounded"></div>
        <div class="h-5 w-20 bg-surface rounded-full"></div>
      </div>
      <div class="h-4 w-48 bg-surface rounded"></div>
      <div class="h-10 w-full bg-surface rounded-xl"></div>
      <div class="flex items-center justify-between pt-2 border-t border-line">
        <div class="h-5 w-28 bg-surface rounded"></div>
        <div class="h-8 w-28 bg-surface rounded-full"></div>
      </div>
    </div>
  </div>

  <!-- State Error -->
  <div id="history-error" class="hidden rounded-2xl border border-line bg-white p-8 text-center">
    <p id="history-error-text" class="text-xs text-danger mb-4">Gagal memuat riwayat pesanan.</p>
    <button
      type="button"
      id="btn-history-retry"
      class="h-9 px-5 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
    >
      Coba Lagi
    </button>
  </div>

  <!-- State Kosong -->
  <div id="history-empty" class="hidden rounded-2xl border border-line bg-white p-10 text-center">
    <div class="w-16 h-16 rounded-full bg-surface flex items-center justify-center mx-auto mb-4 text-subtle">
      <svg class="h-8 w-8 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="2"/>
        <line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>
      </svg>
    </div>
    <h2 class="text-lg font-bold text-ink mb-1.5">Belum ada riwayat pesanan</h2>
    <p class="text-xs text-subtle max-w-sm mx-auto mb-6">
      Anda belum membuat pesanan dari perangkat ini. Jelajahi menu kami dan pesan hidangan favorit Anda!
    </p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
      <a
        href="<?= e(url('index.php')) ?>"
        class="h-11 px-6 rounded-full bg-black text-white text-xs font-medium inline-flex items-center justify-center hover:bg-neutral-800 transition-colors"
      >
        Mulai Pesan Menu
      </a>
      <a
        href="<?= e(url('track.php')) ?>"
        class="h-11 px-6 rounded-full bg-surface text-ink text-xs font-medium inline-flex items-center justify-center hover:bg-neutral-200 transition-colors"
      >
        Lacak dengan Kode
      </a>
    </div>
  </div>

  <!-- Daftar Kartu Pesanan -->
  <div id="history-list" class="hidden space-y-4">
    <!-- Diisi JS -->
  </div>

</div><!-- /history-page -->
