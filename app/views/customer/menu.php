<?php
declare(strict_types=1);

use App\Core\Config;

/**
 * View Katalog & Landing Page Pelanggan — Nola.
 */

$appName = (string) Config::get('app.name', 'Nola');
$panoBase = asset('img/hero/pano/');
$interiorImage = asset('assets/images/tour/tour-4.webp');
$panoFile = PUBLIC_PATH . '/assets/images/panorama/cafe-panorama-seamless.jpg';
$panoVersion = is_file($panoFile) ? (string) filemtime($panoFile) : '2';
?>

<!-- ============================================================
     0. CAFÉ AMBIENT STAGE (Ken Burns — Zero Seam, GPU 60fps)
     Two interior layers crossfade with slow zoom-pan; no tiling,
     no wrap point, no visible join ever.
     ============================================================ -->
<div id="cafe-stage" aria-hidden="true" role="presentation">
  <!-- Layer A: Barista counter — always underneath -->
  <div class="cafe-kb-layer cafe-kb-a">
    <img class="cafe-kb-img" src="<?= e(asset('images/panorama/panel-1.jpg')) ?>?v=<?= e($panoVersion) ?>"
         alt="" loading="eager" decoding="async">
  </div>
  <!-- Layer B: Coffee bar front face — crossfades over A -->
  <div class="cafe-kb-layer cafe-kb-b">
    <img class="cafe-kb-img" src="<?= e(asset('img/hero/pano/f.jpg')) ?>"
         alt="" loading="eager" decoding="async">
  </div>

  <div class="cafe-scrim-left"></div>
  <div class="cafe-vignette"></div>
  <div class="cafe-warm-tone"></div>
</div>
<div class="shade"></div>

<!-- ============================================================
     1. NAVIGATION BAR (Floating Pill — Kopi Calf Inspired)
     ============================================================ -->
<nav class="lp-nav" id="lp-nav" aria-label="Navigasi utama">
  <div class="lp-nav-in">
    <!-- Script Wordmark -->
    <a href="<?= e(url()) ?>" class="lp-logo focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-full" aria-label="<?= e($appName) ?>">
      <span class="lp-logo-text">Nola</span>
    </a>

    <!-- Nav Links -->
    <div class="lp-nav-links">
      <a href="#lp-menu" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" data-i18n="nav_menu"><?= e(__('nav_menu')) ?></a>
      <a href="#lp-steps" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" data-i18n="lp_nav_service"><?= e(__('lp_nav_service')) ?></a>
      <a href="#lp-outlets" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" data-i18n="lp_nav_outlet"><?= e(__('lp_nav_outlet')) ?></a>
      <a href="#menu-catalog-section" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" data-i18n="lp_nav_catalog"><?= e(__('lp_nav_catalog')) ?></a>
      <a href="<?= e(url('orders.php')) ?>" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded" data-i18n="nav_track"><?= e(__('nav_track')) ?></a>
    </div>

    <!-- Language Pill + Cart + Primary CTA -->
    <div class="lp-nav-actions">
      <div class="lang" data-lang-switch aria-label="Bahasa / Language">
        <button type="button" data-l="id" aria-pressed="<?= current_lang() === 'id' ? 'true' : 'false' ?>">ID</button>
        <button type="button" data-l="en" aria-pressed="<?= current_lang() === 'en' ? 'true' : 'false' ?>">EN</button>
      </div>

      <a
        href="<?= e(url('cart.php')) ?>"
        id="navbar-cart-btn"
        class="lp-nav-cart"
        title="<?= e(__('nav_cart')) ?>"
        aria-label="<?= e(__('nav_cart')) ?>"
      >
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span id="navbar-cart-count" class="cart-badge-count">0</span>
      </a>

      <a href="#menu-catalog-section" class="lp-btn lp-nav-cta">
        <span>Pesan Sekarang</span>
        <span class="lp-arrow" aria-hidden="true">&rarr;</span>
      </a>
    </div>
  </div>
</nav>

<!-- ============================================================
     2. HERO — Bottom-left typography over the panorama
     ============================================================ -->
<section class="lp-hero" id="landing-hero">
  <div class="lp-wrap lp-hero-in" id="lp-hero-in">
    <span class="lp-live" data-rv>
      <i aria-hidden="true"></i>
      <span>Diseduh Segar Setiap Hari</span>
    </span>

    <h1 class="lp-title" id="lp-title">
      <span class="ln"><span data-i18n="lp_title_1"><?= e(__('lp_title_1')) ?></span></span>
      <span class="ln"><span><em data-i18n="lp_title_2"><?= e(__('lp_title_2')) ?></em></span></span>
      <span class="ln"><span data-i18n="lp_title_3"><?= e(__('lp_title_3')) ?></span></span>
    </h1>

    <div class="lp-hero-row">
      <p class="lp-lead" data-rv>
        Halo Teman Nola, kamu bisa menikmati seluruh menu racikan kopi, minuman segar, dan hidangan lezat langsung dipesan dari layar kamu.
      </p>

      <div class="lp-ctas" data-rv>
        <a href="#menu-catalog-section" class="lp-btn lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
          <span>Pesan Sekarang</span>
          <span class="lp-arrow" aria-hidden="true">&rarr;</span>
        </a>
        <a href="#lp-steps" class="lp-btn lg ghost focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" id="btn-hero-service" data-i18n="lp_service_cta">
          <?= e(__('lp_service_cta')) ?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Persistent interaction hint -->
<div class="cafe-hint" id="cafe-hint" role="note">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    <rect x="7" y="2" width="10" height="20" rx="5"></rect>
    <path d="M12 6v4"></path>
  </svg>
  <span>Scroll ke bawah untuk melihat menu</span>
</div>

<!-- ============================================================
     3. MENU UNGGULAN & SIGNATURE TRACK (Persis img 3: Pinned Sticky Scroll)
     ============================================================ -->
<section class="lp-menu" id="lp-menu">
  <div class="lp-sticky" id="lp-sticky">
    <div class="lp-wrap lp-menu-head" data-rv>
      <div>
        <p class="lp-k">&mdash; MENU UNGGULAN</p>
        <h2 class="lp-h2">Yang paling sering dipesan</h2>
      </div>
      <div class="lp-progress" id="lp-mprog" title="Progres scroll menu">
        <i id="lp-mprog-bar"></i>
      </div>
    </div>

    <!-- Pinned Horizontal Scroll Card Track (Persis img 3) -->
    <div class="lp-track-container" id="lp-track-container">
      <div class="lp-track" id="lp-track">
        <!-- 1. Es Kopi Nola Premium (Blue bg) -->
        <div class="lp-card group" onclick="$('#mobile-search-input, #global-search-input').val('Es Kopi Susu Premium').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#1</span>
            <img src="<?= e(url('uploads/menus/premium.webp')) ?>" alt="Es Kopi Nola Premium" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>ORIGINAL KOPI SUSU</small>
            <b>Es Kopi Nola Premium</b>
            <span>Rp 20.000</span>
          </div>
        </div>

        <!-- 2. Es Kopi Nola Regular (Red bg) -->
        <div class="lp-card card-red group" onclick="$('#mobile-search-input, #global-search-input').val('Es Kopi Susu Regular').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#2</span>
            <img src="<?= e(url('uploads/menus/reguler.webp')) ?>" alt="Es Kopi Nola Regular" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>ORIGINAL KOPI SUSU</small>
            <b>Es Kopi Nola Regular</b>
            <span>Rp 18.000</span>
          </div>
        </div>

        <!-- 3. New Matcha Latte (Blue bg) -->
        <div class="lp-card group" onclick="$('#mobile-search-input, #global-search-input').val('New Matcha Latte').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#3</span>
            <img src="<?= e(url('uploads/menus/matcha.webp')) ?>" alt="New Matcha Latte" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>MILK SERIES</small>
            <b>New Matcha Latte</b>
            <span>Rp 25.000</span>
          </div>
        </div>

        <!-- 4. Iced Chocolate (Blue bg) -->
        <div class="lp-card group" onclick="$('#mobile-search-input, #global-search-input').val('Iced Chocolate').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#4</span>
            <img src="<?= e(url('uploads/menus/iced-chocolate.webp')) ?>" alt="Iced Chocolate" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>MILK SERIES</small>
            <b>Iced Chocolate</b>
            <span>Rp 25.000</span>
          </div>
        </div>

        <!-- 5. Magic Cheese Cream (Red bg) -->
        <div class="lp-card card-red group" onclick="$('#mobile-search-input, #global-search-input').val('Magic Cheese Cream').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#5</span>
            <img src="<?= e(url('uploads/menus/magic-cheese-butter-cream.webp')) ?>" alt="Magic Cheese Cream" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>MAGIC CREAM</small>
            <b>Magic Cheese Cream</b>
            <span>Rp 27.000</span>
          </div>
        </div>

        <!-- 6. Smooth Pistachio (Blue bg) -->
        <div class="lp-card group" onclick="$('#mobile-search-input, #global-search-input').val('Smooth Pistachio').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#6</span>
            <img src="<?= e(url('uploads/menus/smooth-cup.webp')) ?>" alt="Smooth Pistachio" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>SMOOTH SERIES</small>
            <b>Smooth Pistachio</b>
            <span>Rp 29.000</span>
          </div>
        </div>

        <!-- 7. Dimsum Mentai (Red bg) -->
        <div class="lp-card card-red group" onclick="$('#mobile-search-input, #global-search-input').val('Dimsum Mentai').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#7</span>
            <img src="<?= e(url('uploads/menus/dimsum-mentai.webp')) ?>" alt="Dimsum Mentai" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>DIMSUM</small>
            <b>Dimsum Mentai</b>
            <span>Rp 25.000</span>
          </div>
        </div>

        <!-- 8. Fudgy Brownies (Blue bg) -->
        <div class="lp-card group" onclick="$('#mobile-search-input, #global-search-input').val('Brownies Fudgy').trigger('input'); document.getElementById('menu-catalog-section').scrollIntoView({behavior:'smooth'});">
          <div class="lp-card-ph">
            <span class="lp-rank">#8</span>
            <img src="<?= e(url('uploads/menus/fudgy-brownies.webp')) ?>" alt="Brownies Fudgy" loading="lazy">
          </div>
          <div class="lp-card-body">
            <small>BAKERY</small>
            <b>Fudgy Brownies</b>
            <span>Rp 10.000</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     4. CERITA LAYANAN / PENGALAMAN KAFE (Persis Kopi Calf)
     ============================================================ -->
<section class="lp-steps-section" id="lp-steps">
  <div class="lp-wrap lp-steps-grid">
    <!-- Header Kolom Kiri -->
    <div class="lp-steps-lead" data-rv>
      <p class="lp-k">&mdash; CERITA LAYANAN</p>
      <h2 class="lp-h2 text-3xl sm:text-4xl lg:text-5xl font-bold font-ui text-white leading-tight">
        Dari pintu masuk sampai tegukan pertama
      </h2>
      <p class="mt-4 text-sm sm:text-base leading-relaxed text-gray-400 max-w-sm">
        Pengalaman menikmati hidangan dan racikan kopi favorit dengan alur pemesanan yang mudah dan cepat.
      </p>
      <div class="mt-8">
        <a href="#menu-catalog-section" class="lp-btn sm lp-nav-cta focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
          <span>Pesan Sekarang</span>
          <span class="lp-arrow" aria-hidden="true">&rarr;</span>
        </a>
      </div>
    </div>

    <!-- Timeline Vertikal Kanan (Persis Foto 1 Calf) -->
    <div class="lp-timeline-wrapper" data-rv>
      <ol class="lp-timeline" id="lp-timeline">
        <div class="lp-timeline-fill" id="lp-timeline-fill"></div>

        <!-- Langkah 1 -->
        <li data-step="1">
          <span class="n">1</span>
          <div class="lp-step-content">
            <small>DATANG</small>
            <b>Disambut &ldquo;Halo Teman Nola!&rdquo;</b>
            <p>Begitu pintu terbuka, barista menyapa dan siap membantu kamu memilih.</p>
          </div>
        </li>

        <!-- Langkah 2 -->
        <li data-step="2">
          <span class="n">2</span>
          <div class="lp-step-content">
            <small>PILIH MENU</small>
            <b>Bingung? Tanya rekomendasi</b>
            <p>Lihat papan menu di atas bar. Barista bisa menyarankan rasa sesuai selera &mdash; manis, kuat, atau tanpa kopi.</p>
          </div>
        </li>

        <!-- Langkah 3 -->
        <li data-step="3">
          <span class="n">3</span>
          <div class="lp-step-content">
            <small>PESAN &amp; BAYAR</small>
            <b>Atur sesuai seleramu</b>
            <p>Pesan di kasir, sesuaikan gula dan es, lalu bayar tunai atau non-tunai. Sebutkan namamu untuk dipanggil.</p>
          </div>
        </li>

        <!-- Langkah 4 -->
        <li data-step="4">
          <span class="n">4</span>
          <div class="lp-step-content">
            <small>DIRACIK</small>
            <b>Dibuat segar di bar terbuka</b>
            <p>Espresso ditarik saat dipesan dan dicampur susu segar. Kamu bisa melihat prosesnya langsung.</p>
          </div>
        </li>

        <!-- Langkah 5 -->
        <li data-step="5">
          <span class="n">5</span>
          <div class="lp-step-content">
            <small>AMBIL</small>
            <b>Nama dipanggil, siap dinikmati</b>
            <p>Ambil minumanmu di counter pick-up. Jangan lupa ambil sedotan atau tutup cup sesuai kebutuhan.</p>
          </div>
        </li>

        <!-- Langkah 6 -->
        <li data-step="6">
          <span class="n">6</span>
          <div class="lp-step-content">
            <small>NIKMATI</small>
            <b>Duduk santai atau bawa jalan</b>
            <p>Nikmati suasana outlet yang nyaman dengan Wi-Fi kencang, atau bawa cup-mu untuk menemani harimu.</p>
          </div>
        </li>
      </ol>
    </div>
  </div>
</section>

<!-- ============================================================
     5. KATALOG MENU LENGKAP & PENCARIAN (STYLE KOPI CALF — PUTIH BERSIH)
     Desain Luxury Sheet Melengkung Anggun (Zero Harsh Edge Transition)
     ============================================================ -->
<div id="menu-catalog-section" class="relative z-20 w-full bg-white text-gray-900 shadow-2xl transition-all">
  <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-10 xl:px-14 pt-8 sm:pt-12 pb-12 sm:pb-16 space-y-6 sm:space-y-8">

    <!-- Breadcrumb & Header Khas Calf -->
    <div class="flex flex-col gap-2">
      <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 font-medium" aria-label="Breadcrumb">
        <a href="<?= e(url()) ?>" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 rounded">Home</a>
        <span class="text-gray-400" aria-hidden="true">&gt;</span>
        <span class="text-gray-800 font-semibold" aria-current="page"><?= e($appName) ?></span>
      </nav>
    </div>

    <!-- Baris Chip Kategori (Capsule Pills dengan Icon) & Search -->
    <div id="category-chips-bar" class="-mx-4 px-4 sm:mx-0 sm:px-0 py-3 sm:py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3.5 sm:gap-4 lg:gap-6">
      
      <!-- Container Kategori dengan Scroll Fade & Arrow Navigation -->
      <div class="category-chips-wrapper relative flex-1 min-w-0 flex items-center">
        <!-- Tombol Scroll Kiri (Desktop) -->
        <button
          type="button"
          id="btn-chips-scroll-left"
          class="hidden md:flex absolute left-0 z-20 h-8 w-8 items-center justify-center rounded-full bg-white shadow-md border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-400 transition-all opacity-0 pointer-events-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
          aria-label="Scroll kategori ke kiri"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>

        <!-- Chips Scroll Container dengan Gradient Mask -->
        <div id="category-chips-scroll-area" class="category-chips-scroll-area w-full overflow-x-auto no-scrollbar scroll-smooth py-1 px-4 sm:px-6">
          <div id="category-chips" class="flex items-center gap-2 sm:gap-2.5 w-max pr-6 sm:pr-8" role="tablist" aria-label="Kategori Menu">
            <button
              type="button"
              class="category-chip h-10 px-5 rounded-full text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 bg-blue-600 text-white shadow-sm flex items-center gap-2 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
              data-category-id=""
              role="tab"
              aria-selected="true"
            >
              <span class="text-sm" aria-hidden="true">✨</span>
              <span data-i18n="cat_all"><?= e(__('cat_all')) ?></span>
            </button>
            <!-- Chip kategori lainnya dimuat secara dinamis oleh menu.js -->
          </div>
        </div>

        <!-- Tombol Scroll Kanan (Desktop) -->
        <button
          type="button"
          id="btn-chips-scroll-right"
          class="hidden md:flex absolute right-0 z-20 h-8 w-8 items-center justify-center rounded-full bg-white shadow-md border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-400 transition-all opacity-0 pointer-events-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
          aria-label="Scroll kategori ke kanan"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>

      <!-- Divider Lembut di Desktop -->
      <div class="hidden lg:block w-px h-8 bg-gray-200 shrink-0" aria-hidden="true"></div>

      <!-- Kolom Pencarian Bersih & Modern -->
      <div class="w-full md:w-72 lg:w-80 shrink-0">
        <div class="relative w-full">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none" aria-hidden="true">
            <?= icon('search', 'h-4 w-4 text-gray-400') ?>
          </span>
          <input
            type="search"
            id="global-search-input"
            data-i18n-placeholder="nav_search_placeholder"
            placeholder="Cari menu favoritmu..."
            aria-label="Cari menu makanan atau minuman"
            class="h-10 sm:h-11 w-full rounded-full bg-slate-50 pl-10 pr-9 text-xs sm:text-sm text-slate-800 placeholder-slate-400 border border-slate-200 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all shadow-none"
          >
          <button
            type="button"
            id="btn-clear-search"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 hidden cursor-pointer focus:outline-none"
            aria-label="Hapus pencarian"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Bar Notifikasi Pesanan Aktif -->
    <div id="active-order-banner" class="hidden rounded-2xl border border-blue-100 bg-blue-50/70 p-4 flex items-center justify-between gap-3 text-xs sm:text-sm" role="status">
      <div class="flex items-center gap-2.5 min-w-0">
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white" aria-hidden="true">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </span>
        <p class="truncate text-gray-800 font-medium">
          <span data-i18n="active_order_prefix"><?= e(__('active_order_prefix')) ?></span> <span id="active-order-code" class="font-bold font-mono text-blue-600"></span> <span id="active-order-status-desc"><?= e(__('status_processing')) ?></span>.
        </p>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <a
          id="active-order-link"
          href="<?= e(url('orders.php')) ?>"
          class="font-bold text-blue-600 hover:underline text-xs sm:text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 rounded"
          data-i18n="track_status"
        >
          <?= e(__('track_status')) ?>
        </a>
        <button
          type="button"
          id="btn-dismiss-active-order"
          class="h-8 w-8 rounded-full hover:bg-blue-100 flex items-center justify-center text-gray-400 hover:text-gray-700 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
          title="Tutup notifikasi"
          aria-label="Tutup notifikasi pesanan aktif"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- State Loading (Skeleton Cards Responsive Grid) -->
    <div id="menu-loading" class="space-y-8" aria-busy="true" aria-label="Memuat menu">
      <div class="space-y-4">
        <div class="h-4 w-28 rounded-full bg-slate-200 skeleton-bar"></div>
        <div class="h-7 w-48 rounded-xl bg-slate-200 skeleton-bar"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6 lg:gap-7">
          <div class="skeleton-card rounded-3xl border border-slate-100 bg-white overflow-hidden shadow-sm flex flex-col">
            <div class="w-full aspect-[4/3] bg-slate-100 skeleton-img"></div>
            <div class="p-5 space-y-3">
              <div class="h-5 w-3/4 rounded-full bg-slate-200 skeleton-bar"></div>
              <div class="h-4 w-full rounded-full bg-slate-100 skeleton-bar"></div>
              <div class="h-6 w-1/3 rounded-full bg-slate-200 skeleton-bar mt-4"></div>
            </div>
          </div>
          <div class="skeleton-card rounded-3xl border border-slate-100 bg-white overflow-hidden shadow-sm flex flex-col">
            <div class="w-full aspect-[4/3] bg-slate-100 skeleton-img"></div>
            <div class="p-5 space-y-3">
              <div class="h-5 w-3/4 rounded-full bg-slate-200 skeleton-bar"></div>
              <div class="h-4 w-full rounded-full bg-slate-100 skeleton-bar"></div>
              <div class="h-6 w-1/3 rounded-full bg-slate-200 skeleton-bar mt-4"></div>
            </div>
          </div>
          <div class="skeleton-card rounded-3xl border border-slate-100 bg-white overflow-hidden shadow-sm flex flex-col">
            <div class="w-full aspect-[4/3] bg-slate-100 skeleton-img"></div>
            <div class="p-5 space-y-3">
              <div class="h-5 w-3/4 rounded-full bg-slate-200 skeleton-bar"></div>
              <div class="h-4 w-full rounded-full bg-slate-100 skeleton-bar"></div>
              <div class="h-6 w-1/3 rounded-full bg-slate-200 skeleton-bar mt-4"></div>
            </div>
          </div>
          <div class="skeleton-card rounded-3xl border border-slate-100 bg-white overflow-hidden shadow-sm flex flex-col hidden xl:flex">
            <div class="w-full aspect-[4/3] bg-slate-100 skeleton-img"></div>
            <div class="p-5 space-y-3">
              <div class="h-5 w-3/4 rounded-full bg-slate-200 skeleton-bar"></div>
              <div class="h-4 w-full rounded-full bg-slate-100 skeleton-bar"></div>
              <div class="h-6 w-1/3 rounded-full bg-slate-200 skeleton-bar mt-4"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- State Error -->
    <div id="menu-error" class="hidden py-16 text-center" role="alert">
      <p class="text-sm font-medium text-danger" id="menu-error-message">Gagal memuat katalog menu.</p>
      <button
        type="button"
        id="btn-retry-menu"
        class="mt-3 h-10 px-6 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-semibold transition-all cursor-pointer shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
      >
        Coba Lagi
      </button>
    </div>

    <!-- State Kosong -->
    <div id="menu-empty" class="hidden py-16 text-center">
      <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3" aria-hidden="true">
        <?= icon('search', 'h-6 w-6') ?>
      </div>
      <h3 class="text-base font-bold text-gray-900 font-ui">Menu tidak ditemukan</h3>
      <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto" id="menu-empty-message">Coba gunakan kata kunci pencarian atau kategori lain.</p>
      <button
        type="button"
        id="btn-reset-filter"
        class="mt-4 h-10 px-6 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-semibold transition-all cursor-pointer shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
      >
        Tampilkan Semua Menu
      </button>
    </div>

    <!-- Kontainer Daftar Menu (Dikelompokkan per Kategori) -->
    <div id="menu-container" class="hidden space-y-12">
      <!-- Diisi secara dinamis oleh menu.js -->
    </div>
  </div>
</div>

<!-- ============================================================
     6. OUTLETS & STATS SECTION (Gaya Kopi Calf — Blue 2-Col Banner)
     Persis Referensi Pengguna
     ============================================================ -->
<section class="lp-outlets-section" id="lp-outlets">
  <div class="lp-wrap">
    <div class="lp-outlets-grid">
      <!-- Kolom Kiri: Label, Judul, Tombol -->
      <div class="lp-outlets-left" data-rv>
        <p class="lp-k">&mdash; DALAM ANGKA</p>
        <h2 class="lp-h2 lp-outlets-title">
          Ada outlet <?= e($appName) ?><br>di dekat kamu.
        </h2>
        <div class="mt-8">
          <a href="#lp-outlets" class="lp-btn-outlet-pill">
            <span>Pilih Outlet</span>
            <span class="lp-arrow" aria-hidden="true">&rarr;</span>
          </a>
        </div>
      </div>

      <!-- Kolom Kanan: Stats Counter 3 Kolom + City Pills -->
      <div class="lp-outlets-right" data-rv>
        <!-- Row Stats 3 Kolom dengan Divider Halus -->
        <div class="lp-outlets-stats" id="lp-outlets-stats">
          <div class="lp-outlet-stat">
            <b class="lp-counter" data-target="111">0</b>
            <span>Outlet</span>
          </div>
          <div class="lp-outlet-stat">
            <b class="lp-counter" data-target="36">0</b>
            <span>Kota</span>
          </div>
          <div class="lp-outlet-stat">
            <b class="lp-counter" data-target="103">0</b>
            <span>Menu</span>
          </div>
        </div>

        <!-- City Pills Filter/Tags -->
        <div class="lp-city-pills">
          <span class="lp-city-pill">Jakarta Selatan</span>
          <span class="lp-city-pill">Bekasi</span>
          <span class="lp-city-pill">Jakarta Timur</span>
          <span class="lp-city-pill">Jakarta Barat</span>
          <span class="lp-city-pill">Tangerang</span>
          <span class="lp-city-pill">Jakarta Pusat</span>
          <span class="lp-city-pill">Bandung</span>
          <span class="lp-city-pill">Kab. Bekasi</span>
          <span class="lp-city-pill">Tangerang Selatan</span>
          <span class="lp-city-pill">Depok</span>
          <span class="lp-city-pill lp-city-more">+26</span>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ======================================================= -->
<!-- MODAL DETAIL MENU (Bottom Sheet di Mobile)              -->
<!-- ======================================================= -->
<div id="modal-menu-detail" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="detail-name">
  <!-- Latar belakang gelap -->
  <div class="fixed inset-0 bg-black/60 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <!-- Konten dialog -->
  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl border-t sm:border border-gray-100 max-h-[92vh] overflow-y-auto flex flex-col">
      <!-- Handle drag indicator di mobile -->
      <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mt-3.5 mb-1 sm:hidden" aria-hidden="true"></div>

      <!-- Tombol Tutup Floating / Header (Touch target >= 40px) -->
      <div class="absolute right-4 top-4 z-10">
        <button
          type="button"
          class="h-10 w-10 rounded-full bg-white/90 border border-gray-200/80 flex items-center justify-center text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-all cursor-pointer shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
          data-modal-dismiss
          title="Tutup dialog"
          aria-label="Tutup dialog detail menu"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <!-- Foto Lebar di Atas -->
      <div class="w-full h-52 sm:h-64 bg-gray-100 relative overflow-hidden shrink-0">
        <img
          id="detail-image"
          src=""
          alt=""
          class="w-full h-full object-cover"
        >
        <span id="detail-badge-habis" class="hidden absolute top-4 left-4 px-3.5 py-1.5 rounded-full text-xs font-bold bg-red-600 text-white shadow-md" data-i18n="badge_out_of_stock">
          <?= e(__('badge_out_of_stock')) ?>
        </span>
      </div>

      <!-- Konten Informasi Menu -->
      <div class="p-6 flex-1 flex flex-col justify-between">
        <div>
          <!-- Kategori & Nama -->
          <span id="detail-category" class="text-xs font-bold text-blue-600 uppercase tracking-widest font-mono"></span>
          <h2 id="detail-name" class="text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 mt-1 font-ui leading-tight"></h2>

          <!-- Harga Satuan -->
          <div id="detail-price" class="text-xl font-extrabold text-blue-600 mt-2 font-mono tabular-nums tracking-tight"></div>

          <!-- Deskripsi -->
          <p id="detail-description" class="text-sm text-gray-600 mt-3 leading-relaxed break-words max-w-prose"></p>
        </div>

        <!-- Stepper Jumlah & Tombol Tambah ke Keranjang -->
        <div class="mt-8 pt-4 border-t border-gray-100 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700" data-i18n="qty_label"><?= e(__('qty_label')) ?></span>
            <!-- Stepper Pil -->
            <div class="flex items-center gap-3 bg-gray-100 p-1.5 rounded-full border border-gray-200/80">
              <button
                type="button"
                id="btn-stepper-minus"
                class="w-9 h-9 rounded-full bg-white text-gray-800 flex items-center justify-center shadow-sm hover:bg-gray-50 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                title="Kurangi jumlah"
                aria-label="Kurangi jumlah pesanan"
              >
                <?= icon('minus', 'h-3.5 w-3.5') ?>
              </button>
              <span id="detail-qty" class="w-8 text-center font-bold text-sm text-gray-900 font-mono tabular-nums" aria-live="polite">1</span>
              <button
                type="button"
                id="btn-stepper-plus"
                class="w-9 h-9 rounded-full bg-white text-gray-800 flex items-center justify-center shadow-sm hover:bg-gray-50 active:scale-95 transition-all cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                title="Tambah jumlah"
                aria-label="Tambah jumlah pesanan"
              >
                <?= icon('plus', 'h-3.5 w-3.5') ?>
              </button>
            </div>
          </div>

          <!-- Tombol Biru Kapsul Selebar Penuh "Tambah · Rp xx.xxx" -->
          <button
            type="button"
            id="btn-detail-add"
            class="w-full h-12 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-ui font-bold text-sm sm:text-base transition-all cursor-pointer flex items-center justify-center gap-2 shadow-md hover:shadow-lg disabled:opacity-40 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
          >
            <span id="detail-add-text">Tambah &middot; Rp 0</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
