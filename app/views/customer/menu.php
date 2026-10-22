<?php
declare(strict_types=1);

/**
 * View Katalog Menu Pelanggan (Gaya Uber Eats).
 * FR-CMENU-01 s.d. FR-CMENU-04, prd.md §12.3-12.4.
 */
?>
<div class="space-y-6">
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
  <div class="sticky top-16 z-20 -mx-4 px-4 sm:mx-0 sm:px-0 py-2 bg-white/95 backdrop-blur-sm border-b border-line sm:border-b-0">
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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
        <div class="h-32 rounded-xl border border-line bg-surface/50 p-4 animate-pulse"></div>
      </div>
    </div>
    <div class="space-y-4">
      <div class="h-6 w-40 rounded bg-surface animate-pulse"></div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
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
