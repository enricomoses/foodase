<?php
declare(strict_types=1);

use App\Core\Config;

$appName = (string) Config::get('app.name', 'FoodEase');
?>
<!-- Navbar Lengket Gaya Uber Eats -->
<header class="sticky top-0 z-30 border-b border-line bg-white">
  <div class="mx-auto flex h-16 w-full items-center justify-between px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 gap-4">
    <!-- Wordmark FoodEase Tebal di Kiri -->
    <a href="<?= e(url()) ?>" class="text-xl font-bold tracking-tight text-ink hover:opacity-80 transition-opacity shrink-0">
      <?= e($appName) ?>
    </a>

    <!-- Kolom Cari Pil di Tengah (Tampil di Desktop, disembunyikan di layar kecil) -->
    <div class="hidden md:flex flex-1 max-w-md mx-4">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
          <?= icon('search', 'h-4 w-4') ?>
        </span>
        <input
          type="search"
          id="global-search-input"
          data-i18n-placeholder="nav_search_placeholder"
          placeholder="<?= e(__('nav_search_placeholder')) ?>"
          class="h-10 w-full rounded-full bg-surface pl-10 pr-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        >
      </div>
    </div>

    <!-- Navigasi Kanan: Tautan Pesanan, Bahasa, Toggle Tema, Akun, Keranjang -->
    <nav class="flex items-center gap-2 sm:gap-2.5 shrink-0">
      <a href="<?= e(url('orders.php')) ?>" class="text-sm font-medium text-ink hover:text-subtle transition-colors hidden xs:inline-block" data-i18n="nav_orders">
        <?= e(__('nav_orders')) ?>
      </a>

      <!-- Pemilih Bahasa (ID / EN / ZH) -->
      <div class="relative" id="lang-dropdown-wrapper">
        <button
          type="button"
          id="btn-lang-toggle"
          class="h-9 px-2.5 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center gap-1.5 text-xs font-bold text-ink border border-line cursor-pointer"
          title="Pilih Bahasa / Select Language / 选择语言"
          aria-expanded="false"
        >
          <svg class="h-3.5 w-3.5 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="2" y1="12" x2="22" y2="12"></line>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
          </svg>
          <span id="current-lang-label" class="uppercase font-bold"><?= strtoupper(current_lang()) ?></span>
          <svg class="h-3 w-3 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div
          id="lang-dropdown-menu"
          class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white border border-line shadow-lg py-1.5 z-50 text-xs"
        >
          <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="id">
            <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">ID</span> <span>Indonesia</span></span>
            <span class="lang-check font-bold text-accent <?= current_lang() === 'id' ? '' : 'hidden' ?>">✓</span>
          </button>
          <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="en">
            <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">EN</span> <span>English</span></span>
            <span class="lang-check font-bold text-accent <?= current_lang() === 'en' ? '' : 'hidden' ?>">✓</span>
          </button>
          <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="zh">
            <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">ZH</span> <span>中文 (Mandarin)</span></span>
            <span class="lang-check font-bold text-accent <?= current_lang() === 'zh' ? '' : 'hidden' ?>">✓</span>
          </button>
        </div>
      </div>

      <!-- Tombol Toggle Tema (Dark/Light) -->
      <button
        type="button"
        class="btn-theme-toggle h-9 w-9 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center justify-center text-subtle hover:text-ink cursor-pointer"
        title="Beralih Mode Gelap/Terang"
        aria-label="Ganti tema"
      >
        <!-- Ikon Bulan (Light Mode aktif) -->
        <svg class="icon-moon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
        <!-- Ikon Matahari (Dark Mode aktif) -->
        <svg class="icon-sun h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"/>
          <line x1="12" y1="1" x2="12" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="23"/>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
          <line x1="1" y1="12" x2="3" y2="12"/>
          <line x1="21" y1="12" x2="23" y2="12"/>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
      </button>

      <!-- Tombol Pil Hitam Keranjang · n (dengan Icon Keranjang) -->
      <a
        href="<?= e(url('cart.php')) ?>"
        class="h-10 px-3.5 sm:px-4 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-2 hover:bg-neutral-800 transition-colors shrink-0 shadow-sm"
        aria-label="Keranjang Belanja"
      >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span data-i18n="nav_cart"><?= e(__('nav_cart')) ?></span>
        <span class="opacity-60">&middot;</span>
        <span id="cart-count">0</span>
      </a>
    </nav>

  </div>
</header>
