<?php
declare(strict_types=1);

/**
 * View Katalog Menu Pelanggan (Gaya Uber Eats).
 * FR-CMENU-01 s.d. FR-CMENU-04, prd.md §12.3-12.4.
 */
?>
<div class="space-y-6">
  <!-- Bar Notifikasi Pesanan Aktif (Otomatis tampil jika ada pesanan tersimpan dan berstatus aktif) -->
  <div id="active-order-banner" class="hidden rounded-xl border border-line bg-surface p-3.5 flex items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2.5 min-w-0">
      <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-light text-accent-dark">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </span>
      <p class="truncate text-ink font-medium">
        <span data-i18n="active_order_prefix"><?= e(__('active_order_prefix')) ?></span> <span id="active-order-code" class="font-bold"></span> <span id="active-order-status-desc"><?= e(__('status_processing')) ?></span>.
      </p>
    </div>
    <div class="flex items-center gap-3 shrink-0">
      <a
        id="active-order-link"
        href="<?= e(url('orders.php')) ?>"
        class="font-bold text-ink hover:underline text-xs"
        data-i18n="track_status"
      >
        <?= e(__('track_status')) ?>
      </a>
      <button
        type="button"
        id="btn-dismiss-active-order"
        class="h-6 w-6 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 flex items-center justify-center text-subtle hover:text-ink transition-colors cursor-pointer"
        title="Tutup notifikasi"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- GLACÉ Cafe Landing Page (Full Motion Graphics & 4-Slide Stage — Pinterest Pin 1082693566762069480) -->
  <section
    id="glace-hero-stage"
    class="glace-hero-container -mx-4 sm:-mx-8 lg:-mx-12 xl:-mx-16 2xl:-mx-20 -mt-4 sm:-mt-6 mb-8 rounded-none sm:rounded-2xl border-b sm:border border-white/10"
    data-active-slide="0"
    aria-roledescription="carousel"
    aria-label="Glacé Specialty Cafe Showcase"
  >
    <!-- Watermark Tipografi Raksasa di Latar Belakang (Ghost Typography) -->
    <div class="glace-bg-text-wrap" aria-hidden="true">
      <div id="glace-ghost-text" class="glace-bg-text">Iced Latte Espresso</div>
    </div>

    <!-- Header Top Minimalis Glacé -->
    <div class="glace-top-bar">
      <a href="<?= e(url()) ?>" class="glace-brand-logo">
        Glac&eacute;
      </a>
      <nav class="flex items-center gap-6 sm:gap-8 glace-nav-menu">
        <a href="<?= e(url()) ?>" class="glace-nav-item is-active" data-i18n="nav_home"><?= e(__('nav_home')) ?></a>
        <a href="#category-chips" class="glace-nav-item" data-i18n="nav_menu"><?= e(__('nav_menu')) ?></a>
        <a href="#category-chips" class="glace-nav-item">Shop</a>
        <a href="<?= e(url('orders.php')) ?>" class="glace-nav-item" data-i18n="nav_track"><?= e(__('nav_track')) ?></a>
      </nav>
    </div>

    <!-- Panggung Hero Stage Tiga Kolom: Kiri, Tengah, Kanan -->
    <div class="glace-stage-grid">
      <!-- Kolom Kiri: Headline Serif, Deskripsi, Tombol Order Now -->
      <div class="glace-col-left">
        <h1 class="glace-title-headline">
          <span id="glace-title-1">Iced Latte</span>
          <span id="glace-title-2">&amp; Double Espresso</span>
        </h1>
        <p id="glace-desc" class="glace-product-desc">
          Two shots of house espresso poured over fresh milk and clear ice, silky, balanced and gently sweet.
        </p>
        <div class="pt-2">
          <button
            type="button"
            id="glace-order-btn"
            class="glace-btn-order"
            data-menu-id="37"
            data-menu-name="Iced Latte"
            data-menu-price="35000"
          >
            <span id="glace-order-text">Order Now</span>
          </button>
        </div>
      </div>

      <!-- Kolom Tengah: Wadah Cup Melayang & Floating Ingredients -->
      <div class="glace-col-center">
        <div class="glace-center-stage">
          <!-- Wadah Cup Minuman Utama Glacé -->
          <div class="glace-cup-box">
            <img
              id="glace-cup-img"
              src="<?= e(asset('img/hero/cup_latte.png')) ?>"
              alt="Iced Latte & Double Espresso"
              class="glace-cup-img"
            >
          </div>

          <!-- 6 Partikel Melayang dengan Efek Depth-of-Field (Biji Kopi, Daun Teh, Cokelat, Lemon) -->
          <img id="glace-p-1" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-1" alt="" aria-hidden="true">
          <img id="glace-p-2" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-2" alt="" aria-hidden="true">
          <img id="glace-p-3" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-3" alt="" aria-hidden="true">
          <img id="glace-p-4" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-4" alt="" aria-hidden="true">
          <img id="glace-p-5" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-5" alt="" aria-hidden="true">
          <img id="glace-p-6" src="<?= e(asset('img/hero/coffee-bean.svg')) ?>" class="glace-particle glace-p-6" alt="" aria-hidden="true">
        </div>
      </div>

      <!-- Kolom Kanan: Tagline Italic Serif, Deskripsi Kecil, 4 Kartu Thumbnail Selector Horisontal -->
      <div class="glace-col-right">
        <p class="glace-tagline-italic">
          Slow Brewed, Served Cold
        </p>
        <p id="glace-sub-desc" class="glace-tagline-sub">
          Small-batch ingredients over crystal-clear ice. Refreshing, smooth and made to order.
        </p>

        <!-- 4 Kartu Thumbnail Selector Horisontal -->
        <div class="glace-selectors-row" id="glace-selectors-row" role="tablist" aria-label="Pilih Minuman Khas Glacé">
          <!-- Slide 1: Iced Latte -->
          <button
            type="button"
            class="glace-thumb-card is-active"
            data-slide="0"
            role="tab"
            aria-selected="true"
            title="Iced Latte &amp; Double Espresso"
          >
            <img src="<?= e(asset('img/hero/thumb_latte.png')) ?>" class="glace-thumb-img" alt="Iced Latte">
          </button>

          <!-- Slide 2: Iced Chocolate & Dark Cocoa -->
          <button
            type="button"
            class="glace-thumb-card"
            data-slide="1"
            role="tab"
            aria-selected="false"
            title="Iced Chocolate &amp; Dark Cocoa"
          >
            <img src="<?= e(asset('img/hero/thumb_choco.png')) ?>" class="glace-thumb-img" alt="Iced Chocolate">
          </button>

          <!-- Slide 3: Iced Matcha & Ceremonial Green -->
          <button
            type="button"
            class="glace-thumb-card"
            data-slide="2"
            role="tab"
            aria-selected="false"
            title="Iced Matcha &amp; Ceremonial Green"
          >
            <img src="<?= e(asset('img/hero/thumb_matcha.png')) ?>" class="glace-thumb-img" alt="Iced Matcha">
          </button>

          <!-- Slide 4: Iced Tea & Sicilian Lemon -->
          <button
            type="button"
            class="glace-thumb-card"
            data-slide="3"
            role="tab"
            aria-selected="false"
            title="Iced Tea &amp; Sicilian Lemon"
          >
            <img src="<?= e(asset('img/hero/thumb_lemon.png')) ?>" class="glace-thumb-img" alt="Iced Tea &amp; Lemon">
          </button>
        </div>
      </div>
    </div>

    <!-- Footer Bawah Glacé Minimalis (Sosial Media & Kategori Pintas) -->
    <div class="glace-bottom-bar">
      <div class="flex items-center gap-2 text-xs text-neutral-400">
        <span class="h-2 w-2 rounded-full bg-[#D2A066] animate-pulse"></span>
        <span class="tracking-wide text-white/80">Fresh Daily &bull; Dine-in &amp; Takeaway</span>
      </div>

      <!-- Ikon Sosial Media Minimalis -->
      <div class="glace-social-links">
        <a href="https://instagram.com" target="_blank" rel="noopener" class="glace-social-link" title="Instagram" aria-label="Instagram">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
          </svg>
        </a>
        <a href="https://tiktok.com" target="_blank" rel="noopener" class="glace-social-link" title="TikTok" aria-label="TikTok">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Kolom Cari Pil di Layar Mobile (di desktop tampil di navbar) -->
  <div class="block md:hidden">
    <div class="relative w-full">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
        <?= icon('search', 'h-4 w-4') ?>
      </span>
      <input
        type="search"
        id="mobile-search-input"
        placeholder="<?= e(__('nav_search_placeholder')) ?>"
        data-i18n-placeholder="nav_search_placeholder"
        class="h-11 w-full rounded-full bg-surface pl-10 pr-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
      >
    </div>
  </div>

  <!-- Baris Chip Kategori (Scroll horizontal tanpa scrollbar) -->
  <div class="sticky top-16 z-20 -mx-4 px-4 sm:mx-0 sm:px-0 py-2 bg-white border-b border-line sm:border-b-0">
    <div id="category-chips" class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
      <button
        type="button"
        class="category-chip h-9 px-4 rounded-full text-xs font-semibold whitespace-nowrap transition-colors bg-black text-white cursor-pointer"
        data-category-id=""
      >
        <span data-i18n="cat_all"><?= e(__('cat_all')) ?></span>
      </button>
      <!-- Chip kategori lainnya dimuat secara dinamis -->
    </div>
  </div>


  <!-- State Loading (Skeleton Sederhana Abu-abu) -->
  <div id="menu-loading" class="space-y-8">
    <div class="space-y-4">
      <div class="h-6 w-32 rounded bg-surface animate-pulse"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="h-6 w-40 rounded bg-surface animate-pulse"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
      </div>
    </div>
  </div>

  <!-- State Error -->
  <div id="menu-error" class="hidden py-16 text-center">
    <p class="text-sm font-medium text-danger" id="menu-error-message">Gagal memuat katalog menu.</p>
    <button
      type="button"
      id="btn-retry-menu"
      class="mt-3 h-10 px-5 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
    >
      Coba Lagi
    </button>
  </div>

  <!-- State Kosong -->
  <div id="menu-empty" class="hidden py-16 text-center">
    <div class="mx-auto w-12 h-12 rounded-full bg-surface flex items-center justify-center text-subtle mb-3">
      <?= icon('search', 'h-6 w-6') ?>
    </div>
    <h3 class="text-base font-bold text-ink">Menu tidak ditemukan</h3>
    <p class="mt-1 text-sm text-subtle" id="menu-empty-message">Coba gunakan kata kunci pencarian atau kategori lain.</p>
    <button
      type="button"
      id="btn-reset-filter"
      class="mt-4 h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
    >
      Tampilkan Semua Menu
    </button>
  </div>

  <!-- Kontainer Daftar Menu (Dikelompokkan per Kategori) -->
  <div id="menu-container" class="hidden space-y-10">
    <!-- Diisi secara dinamis oleh menu.js -->
  </div>
</div>

<!-- ======================================================= -->
<!-- MODAL DETAIL MENU (Bottom Sheet di Mobile)              -->
<!-- ======================================================= -->
<div id="modal-menu-detail" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <!-- Latar belakang gelap -->
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <!-- Konten dialog -->
  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white shadow-xl border-t sm:border border-line max-h-[92vh] overflow-y-auto flex flex-col">
      <!-- Handle drag indicator di mobile -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>

      <!-- Tombol Tutup Floating / Header -->
      <div class="absolute right-4 top-4 z-10">
        <button
          type="button"
          class="h-9 w-9 rounded-full bg-white border border-line flex items-center justify-center text-ink hover:bg-surface transition-colors cursor-pointer"
          data-modal-dismiss
          aria-label="Tutup"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <!-- Foto Lebar di Atas -->
      <div class="w-full h-52 sm:h-60 bg-surface relative overflow-hidden shrink-0">
        <img
          id="detail-image"
          src=""
          alt=""
          class="w-full h-full object-cover"
        >
        <span id="detail-badge-habis" class="hidden absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold bg-danger text-white shadow" data-i18n="badge_out_of_stock">
          <?= e(__('badge_out_of_stock')) ?>
        </span>
      </div>

      <!-- Konten Informasi Menu -->
      <div class="p-5 sm:p-6 flex-1 flex flex-col">
        <!-- Kategori & Nama -->
        <span id="detail-category" class="text-xs font-medium text-subtle uppercase tracking-wider"></span>
        <h2 id="detail-name" class="text-xl sm:text-2xl font-bold tracking-tight text-ink mt-1"></h2>

        <!-- Harga Satuan -->
        <div id="detail-price" class="text-lg font-bold text-ink mt-2 tabular-nums"></div>

        <!-- Deskripsi -->
        <p id="detail-description" class="text-sm text-subtle mt-3 leading-relaxed break-words"></p>

        <!-- Stepper Jumlah & Tombol Tambah ke Keranjang -->
        <div class="mt-8 pt-4 border-t border-line space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-subtle" data-i18n="qty_label"><?= e(__('qty_label')) ?></span>
            <!-- Stepper Pil -->
            <div class="flex items-center gap-3 bg-surface p-1 rounded-full border border-line">
              <button
                type="button"
                id="btn-stepper-minus"
                class="w-8 h-8 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                title="Kurangi"
              >
                <?= icon('minus', 'h-3.5 w-3.5') ?>
              </button>
              <span id="detail-qty" class="w-7 text-center font-bold text-sm text-ink tabular-nums">1</span>
              <button
                type="button"
                id="btn-stepper-plus"
                class="w-8 h-8 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 transition-all"
                title="Tambah"
              >
                <?= icon('plus', 'h-3.5 w-3.5') ?>
              </button>
            </div>
          </div>

          <!-- Tombol Hitam Selebar Penuh "Tambah · Rp xx.xxx" -->
          <button
            type="button"
            id="btn-detail-add"
            class="w-full h-12 rounded-full bg-black text-white font-medium hover:bg-neutral-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center gap-1.5"
          >
            <span id="detail-add-text">Tambah &middot; Rp 0</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
