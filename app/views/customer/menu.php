<?php
declare(strict_types=1);

/**
 * View Katalog Menu Pelanggan (Gaya Uber Eats).
 * FR-CMENU-01 s.d. FR-CMENU-04, prd.md §12.3-12.4.
 */
?>
<div class="space-y-6">
  <!-- Bar Notifikasi Pesanan Aktif (Otomatis tampil jika ada pesanan tersimpan) -->
  <div id="active-order-banner" class="hidden rounded-xl border border-line bg-surface p-3.5 flex items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2.5 min-w-0">
      <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-light text-accent-dark">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </span>
      <p class="truncate text-ink font-medium">
        Pesanan aktif <span id="active-order-code" class="font-bold"></span> sedang disiapkan.
      </p>
    </div>
    <a
      id="active-order-link"
      href="<?= e(url('orders.php')) ?>"
      class="shrink-0 font-bold text-ink hover:underline text-xs"
    >
      Lacak Status &rarr;
    </a>
  </div>

  <!-- Hero Cafe Showcase Carousel (Interaktif & Appetizing dengan Background Foto Jelas & Promo Visual) -->
  <div class="relative overflow-hidden rounded-2xl border border-line bg-neutral-900 text-white shadow-lg group" id="hero-carousel-wrap">
    <!-- Ambient Warm Cafe Lighting Glow -->
    <div class="absolute -top-16 -left-16 w-72 h-72 rounded-full bg-amber-500/15 blur-3xl pointer-events-none z-10"></div>
    <div class="absolute -bottom-16 right-10 w-80 h-80 rounded-full bg-orange-500/15 blur-3xl pointer-events-none z-10"></div>

    <!-- Slides Container -->
    <div id="hero-carousel-slides" class="relative w-full h-[360px] sm:h-[330px] md:h-[360px] overflow-hidden">

      <!-- Slide 1: Cafe Utama & Kopi Pilihan -->
      <div class="hero-slide active absolute inset-0 w-full h-full flex items-center justify-between transition-all duration-700 opacity-100 z-10">
        <!-- Background Foto dengan Gradien Halus Agar Foto Jelas & Cerah -->
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= e(url('uploads/banners/hero_slide_1.jpg')) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-black/20"></div>

        <!-- Konten Slide 1 -->
        <div class="relative z-10 max-w-xl p-6 sm:p-8 lg:p-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/25 text-xs font-semibold text-white shadow-sm mb-3">
            <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
            <span>Buka Sekarang &bull; Meja & Bawa Pulang</span>
          </div>
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight drop-shadow-sm">
            Kopi Istimewa & Santapan Hangat Kafe
          </h1>
          <p class="text-xs sm:text-sm text-neutral-200 mt-2 leading-relaxed drop-shadow-sm max-w-md">
            Pesan menu favorit Anda langsung dari meja tanpa harus antre di kasir. Segar, nikmat, dan disajikan tepat waktu.
          </p>
          <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] font-medium text-white/95">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md border border-white/20">
              <?= icon('clock', 'h-3.5 w-3.5 text-accent') ?>
              <span>Penyajian Cepat 10 Menit</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md border border-white/20">
              <?= icon('check', 'h-3.5 w-3.5 text-accent') ?>
              <span>100% Biji Kopi Arabika</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md border border-white/20">
              <?= icon('receipt', 'h-3.5 w-3.5 text-accent') ?>
              <span>QRIS & Tunai</span>
            </span>
          </div>
        </div>

      </div>

      <!-- Slide 2: Promo Sarapan & Cold Brew -->
      <div class="hero-slide absolute inset-0 w-full h-full flex items-center justify-between transition-all duration-700 opacity-0 pointer-events-none z-0">
        <!-- Background Foto Slide 2 Cerah -->
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= e(url('uploads/banners/hero_slide_2.jpg')) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-black/20"></div>

        <!-- Konten Slide 2 -->
        <div class="relative z-10 max-w-xl p-6 sm:p-8 lg:p-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/30 backdrop-blur-md border border-amber-400/40 text-xs font-semibold text-amber-300 shadow-sm mb-3">
            <span>🔥 Promo Sarapan Hemat &bull; Diskon 15%</span>
          </div>
          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight drop-shadow-sm">
            Cold Brew Segar & Pastry Hangat
          </h2>
          <p class="text-xs sm:text-sm text-neutral-200 mt-2 leading-relaxed drop-shadow-sm max-w-md">
            Diseduh dingin selama 12 jam untuk rasa kopi yang halus tanpa rasa asam tajam, dipadu croissant renyah mentega pilihan.
          </p>
          <div class="mt-4 flex items-center gap-3">
            <button
              type="button"
              class="btn-filter-category-quick h-10 px-5 rounded-full bg-white text-black text-xs font-bold hover:bg-neutral-200 transition-colors cursor-pointer flex items-center gap-1.5 shadow"
              data-cat-name="Kopi"
            >
              <span>Jelajah Menu Kopi</span>
              <span>&rarr;</span>
            </button>
            <span class="text-[11px] text-neutral-300">Tersedia Setiap Pagi</span>
          </div>
        </div>

      </div>

      <!-- Slide 3: Makan Siang & Rice Bowl -->
      <div class="hero-slide absolute inset-0 w-full h-full flex items-center justify-between transition-all duration-700 opacity-0 pointer-events-none z-0">
        <!-- Background Foto Slide 3 Cerah -->
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= e(url('uploads/banners/hero_slide_3.jpg')) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-black/20"></div>

        <!-- Konten Slide 3 -->
        <div class="relative z-10 max-w-xl p-6 sm:p-8 lg:p-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/30 backdrop-blur-md border border-rose-400/40 text-xs font-semibold text-rose-300 shadow-sm mb-3">
            <span>🍛 Menu Santap Siang Favorit</span>
          </div>
          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight drop-shadow-sm">
            Ayam Geprek & Nasi Goreng Spesial
          </h2>
          <p class="text-xs sm:text-sm text-neutral-200 mt-2 leading-relaxed drop-shadow-sm max-w-md">
            Pilihan pas santap siang! Olahan ayam krispi bumbu geprek dan nasi goreng kampung dengan telur mata sapi hangat.
          </p>
          <div class="mt-4 flex items-center gap-3">
            <button
              type="button"
              class="btn-filter-category-quick h-10 px-5 rounded-full bg-white text-black text-xs font-bold hover:bg-neutral-200 transition-colors cursor-pointer flex items-center gap-1.5 shadow"
              data-cat-name="Makanan Berat"
            >
              <span>Pesan Makan Siang</span>
              <span>&rarr;</span>
            </button>
            <span class="text-[11px] text-neutral-300">Porsi Mantap</span>
          </div>
        </div>

      </div>

    </div>

    <!-- Tombol Navigasi Panah Kiri & Kanan (Muncul saat Hover di Layar Lebar) -->
    <button
      type="button"
      id="btn-hero-slide-prev"
      class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white backdrop-blur-sm border border-white/20 flex items-center justify-center transition-all opacity-80 hover:opacity-100 cursor-pointer shadow"
      aria-label="Slide sebelumnya"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button
      type="button"
      id="btn-hero-slide-next"
      class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white backdrop-blur-sm border border-white/20 flex items-center justify-center transition-all opacity-80 hover:opacity-100 cursor-pointer shadow"
      aria-label="Slide berikutnya"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>

    <!-- Indikator Dots Slide di Bawah -->
    <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5" id="hero-carousel-dots">
      <button type="button" class="hero-dot w-7 h-1.5 rounded-full bg-white transition-all cursor-pointer" data-slide="0" aria-label="Slide 1"></button>
      <button type="button" class="hero-dot w-2 h-1.5 rounded-full bg-white/40 hover:bg-white/70 transition-all cursor-pointer" data-slide="1" aria-label="Slide 2"></button>
      <button type="button" class="hero-dot w-2 h-1.5 rounded-full bg-white/40 hover:bg-white/70 transition-all cursor-pointer" data-slide="2" aria-label="Slide 3"></button>
    </div>
  </div>

  <!-- Kolom Cari Pil di Layar Mobile (di desktop tampil di navbar) -->
  <div class="block md:hidden">
    <div class="relative w-full">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
        <?= icon('search', 'h-4 w-4') ?>
      </span>
      <input
        type="search"
        id="mobile-search-input"
        placeholder="Cari makanan atau minuman..."
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
        Semua
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
          class="h-9 w-9 rounded-full bg-white/90 backdrop-blur-sm shadow border border-line flex items-center justify-center text-ink hover:bg-white transition-colors"
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
        <span id="detail-badge-habis" class="hidden absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold bg-danger text-white shadow">
          Habis
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
            <span class="text-sm font-medium text-subtle">Jumlah</span>
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
