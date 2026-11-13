<?php
declare(strict_types=1);

/**
 * View Halaman Keranjang Belanja Pelanggan.
 * FR-CART-01..04, prd.md §6.6, §12.3-12.4.
 */
?>
<div id="cart-page" class="w-full max-w-5xl mx-auto">

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

  <!-- ==================== STATE: KOSONG (Gaya Image 1 - Appetizing & Engaging) ==================== -->
  <div id="cart-empty" class="hidden flex flex-col items-center justify-center py-14 sm:py-20 text-center px-4">
    <!-- Ilustrasi Toko Kafe & Burger Berwarna (Persis Image 1) -->
    <div class="relative w-44 h-44 sm:w-52 sm:h-52 mb-6 flex items-center justify-center">
      <div class="w-40 h-40 sm:w-48 sm:h-48 rounded-full bg-gradient-to-b from-amber-100 to-orange-50 dark:from-neutral-800 dark:to-neutral-900 border-4 border-white dark:border-neutral-700 shadow-xl flex items-center justify-center relative overflow-hidden">
        <!-- SVG Toko Kafe & Burger Lezat Berwarna -->
        <svg class="w-32 h-32 sm:w-36 sm:h-36" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Tenda Awning Garis Merah Putih -->
          <path d="M25 50 C25 45 35 42 80 42 C125 42 135 45 135 50 L140 70 C140 73 137 76 133 76 C129 76 126 73 126 70 C126 73 123 76 119 76 C115 76 112 73 112 70 C112 73 109 76 105 76 C101 76 98 73 98 70 C98 73 95 76 91 76 C87 76 84 73 84 70 C84 73 81 76 77 76 C73 76 70 73 70 70 C70 73 67 76 63 76 C59 76 56 73 56 70 C56 73 53 76 49 76 C45 76 42 73 42 70 C42 73 39 76 35 76 C31 76 28 73 28 70 Z" fill="#EF4444"/>
          <path d="M42 43 L42 76 C45 76 49 73 49 70 L49 43 Z M70 42 L70 76 C73 76 77 73 77 70 L77 42 Z M98 42 L98 76 C101 76 105 73 105 70 L105 42 Z M126 43 L126 76 C129 76 133 73 133 70 L133 43 Z" fill="#FFFFFF"/>
          
          <!-- Jendela Toko Kafe Biru Cerah -->
          <rect x="36" y="70" width="88" height="52" rx="4" fill="#E0F2FE"/>
          <line x1="80" y1="70" x2="80" y2="122" stroke="#BAE6FD" stroke-width="2"/>
          <rect x="30" y="118" width="100" height="8" rx="2" fill="#F59E0B"/>

          <!-- Burger Berlapis Lezat di Tengah Jendela -->
          <!-- Bun Atas -->
          <path d="M56 86 C56 75 104 75 104 86 Z" fill="#D97706"/>
          <ellipse cx="68" cy="80" rx="1.5" ry="1" fill="#FEF3C7"/>
          <ellipse cx="80" cy="78" rx="1.5" ry="1" fill="#FEF3C7"/>
          <ellipse cx="92" cy="81" rx="1.5" ry="1" fill="#FEF3C7"/>
          <!-- Selada Hijau -->
          <path d="M52 87 Q60 84 68 87 Q76 90 84 87 Q92 84 100 87 Q104 88 108 87 L106 91 L54 91 Z" fill="#22C55E"/>
          <!-- Daging Patty Cokelat -->
          <rect x="54" y="91" width="52" height="8" rx="4" fill="#78350F"/>
          <!-- Keju Leleh Kuning -->
          <path d="M55 95 L68 95 L72 101 L76 95 L105 95 L105 98 L55 98 Z" fill="#FBBF24"/>
          <!-- Tomat Merah -->
          <rect x="58" y="98" width="44" height="4" rx="2" fill="#EF4444"/>
          <!-- Bun Bawah -->
          <path d="M56 102 C56 108 104 108 104 102 Z" fill="#D97706"/>
        </svg>
      </div>
    </div>

    <!-- Teks Headline & Subtitle Menarik (Persis Image 1) -->
    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-ink" data-i18n="cart_empty_title">
      <?= e(__('cart_empty_title')) ?>
    </h2>
    <p class="mt-2 text-sm text-subtle max-w-sm mx-auto leading-relaxed" data-i18n="cart_empty_desc">
      <?= e(__('cart_empty_desc')) ?>
    </p>

    <!-- Tombol Aksi Utama Berbentuk Pil -->
    <a
      href="<?= e(url('index.php')) ?>"
      class="mt-6 h-12 px-8 rounded-full bg-black text-white text-sm font-bold inline-flex items-center gap-2 hover:bg-neutral-800 transition-all shadow-md hover:shadow-lg cursor-pointer transform hover:-translate-y-0.5"
    >
      <span data-i18n="cart_btn_explore"><?= e(__('cart_btn_explore')) ?></span>
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>
  </div>

  <!-- ==================== KONTEN UTAMA ==================== -->
  <div id="cart-content" class="hidden">

    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <h1 class="text-2xl font-bold tracking-tight text-ink" data-i18n="cart_title"><?= e(__('cart_title')) ?></h1>
      <button
        type="button"
        id="btn-clear-cart"
        class="text-xs font-medium text-danger hover:underline transition-colors cursor-pointer"
        data-i18n="cart_btn_clear"
      >
        <?= e(__('cart_btn_clear')) ?>
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
        <span class="text-sm text-subtle font-medium" data-i18n="cart_subtotal"><?= e(__('cart_subtotal')) ?></span>
        <span id="cart-subtotal" class="text-sm font-semibold text-ink tabular-nums">Rp 0</span>
      </div>

      <!-- Tombol Checkout & Kembali -->
      <div class="flex flex-col sm:flex-row gap-3 mt-4">
        <a
          href="<?= e(url('index.php')) ?>"
          class="flex-1 h-11 rounded-full bg-surface border border-line text-ink text-sm font-bold inline-flex items-center justify-center hover:bg-neutral-200 transition-colors"
          data-i18n="checkout_back"
        >
          <?= e(__('checkout_back')) ?>
        </a>
        <a
          href="<?= e(url('checkout.php')) ?>"
          id="btn-checkout"
          class="flex-1 h-11 rounded-full bg-black text-white text-sm font-medium inline-flex items-center justify-center hover:bg-neutral-800 transition-colors"
          data-i18n="checkout_btn"
        >
          <?= e(__('checkout_btn')) ?>
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
