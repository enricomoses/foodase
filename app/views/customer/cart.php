<?php
declare(strict_types=1);

/**
 * View Halaman Keranjang Belanja Pelanggan.
 * FR-CART-01..04, prd.md §6.6, §12.3-12.4.
 */
?>
<div id="cart-page" class="max-w-3xl mx-auto">

  <!-- ==================== STATE: LOADING ==================== -->
  <div id="cart-loading" class="space-y-4 py-4">
    <!-- Skeleton header -->
    <div class="h-7 w-40 rounded bg-surface animate-pulse"></div>
    <!-- Skeleton rows -->
    <?php for ($i = 0; $i < 3; $i++): ?>
    <div class="flex items-center gap-4 p-4 rounded-xl border border-line bg-white animate-pulse">
      <div class="w-16 h-16 rounded-xl bg-surface shrink-0"></div>
      <div class="flex-1 space-y-2">
        <div class="h-4 w-3/4 rounded bg-surface"></div>
        <div class="h-3 w-1/4 rounded bg-surface"></div>
      </div>
      <div class="w-24 h-8 rounded-full bg-surface"></div>
    </div>
    <?php endfor; ?>
  </div>

  <!-- ==================== STATE: KOSONG ==================== -->
  <div id="cart-empty" class="hidden flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-full bg-surface flex items-center justify-center text-subtle mb-4">
      <!-- Ikon keranjang (SVG outline) -->
      <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
      </svg>
    </div>
    <h2 class="text-lg font-bold text-ink">Keranjang kosong</h2>
    <p class="mt-1 text-sm text-subtle">Tambahkan menu terlebih dahulu untuk melanjutkan.</p>
    <a
      href="<?= e(url('index.php')) ?>"
      class="mt-6 h-11 px-6 rounded-full bg-black text-white text-sm font-medium inline-flex items-center hover:bg-neutral-800 transition-colors"
    >
      Lihat Menu
    </a>
  </div>

  <!-- ==================== KONTEN UTAMA ==================== -->
  <div id="cart-content" class="hidden">

    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <h1 class="text-2xl font-bold tracking-tight text-ink">Keranjang</h1>
      <button
        type="button"
        id="btn-clear-cart"
        class="text-xs font-medium text-danger hover:underline transition-colors cursor-pointer"
      >
        Kosongkan
      </button>
    </div>

    <!-- Banner item tidak tersedia (tersembunyi secara default) -->
    <div id="cart-unavailable-banner" class="hidden mb-4 flex items-start gap-2 rounded-xl border border-danger-light bg-danger-light/40 px-4 py-3">
      <svg class="h-4 w-4 text-danger shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p class="text-xs text-danger leading-relaxed">
        Beberapa item sudah tidak tersedia dan ditandai. Hapus item tersebut untuk melanjutkan checkout.
      </p>
    </div>

    <!-- Daftar Item Keranjang -->
    <div id="cart-items" class="space-y-3">
      <!-- Diisi oleh cart.js -->
    </div>

    <!-- Ringkasan & Tombol Checkout -->
    <div id="cart-summary"
         class="mt-6 sticky bottom-0 bg-white border-t border-line pt-4 pb-6 sm:static sm:border-t-0 sm:pt-0 sm:pb-0">

      <!-- Total -->
      <div class="flex items-center justify-between py-3 border-t border-line first:border-t-0">
        <span class="text-sm text-subtle font-medium">Subtotal</span>
        <span id="cart-subtotal" class="text-sm font-semibold text-ink tabular-nums">Rp 0</span>
      </div>

      <!-- Tombol Checkout & Kembali -->
      <div class="flex flex-col sm:flex-row gap-3 mt-4">
        <a
          href="<?= e(url('index.php')) ?>"
          class="flex-1 h-11 rounded-full bg-surface text-ink text-sm font-medium inline-flex items-center justify-center hover:bg-neutral-200 transition-colors"
        >
          ← Kembali ke Menu
        </a>
        <a
          href="<?= e(url('checkout.php')) ?>"
          id="btn-checkout"
          class="flex-1 h-11 rounded-full bg-black text-white text-sm font-medium inline-flex items-center justify-center hover:bg-neutral-800 transition-colors"
        >
          Checkout
        </a>
      </div>
    </div>

  </div><!-- /cart-content -->

</div>

<!-- Modal Konfirmasi Kosongkan -->
<div id="modal-clear-confirm" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-dismiss></div>
  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-sm rounded-t-2xl sm:rounded-2xl bg-white shadow-xl p-6">
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-5 sm:hidden"></div>
      <h3 class="text-base font-bold text-ink">Kosongkan keranjang?</h3>
      <p class="mt-1.5 text-sm text-subtle">Semua item akan dihapus dari keranjang.</p>
      <div class="flex gap-3 mt-6">
        <button
          type="button"
          id="btn-clear-cancel"
          data-modal-dismiss
          class="flex-1 h-11 rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          id="btn-clear-confirm"
          class="flex-1 h-11 rounded-full bg-danger text-white text-sm font-medium hover:bg-red-700 transition-colors cursor-pointer"
        >
          Kosongkan
        </button>
      </div>
    </div>
  </div>
</div>
