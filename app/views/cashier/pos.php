<?php
declare(strict_types=1);

/**
 * View POS Kasir — Pembuatan pesanan langsung oleh kasir.
 * FR-POS-01..03, prd.md §6.10, §4.3, §12.4 "Kasir – POS".
 */
?>

<div id="cashier-pos-page" class="space-y-6">

  <!-- Header Ringkas Halaman POS -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Point of Sale (POS)</h1>
      <p class="text-xs text-subtle mt-0.5">Buat pesanan langsung pelanggan di kasir.</p>
    </div>
  </div>

  <!-- Layout Dua Kolom: Kiri Menu & Pencarian, Kanan Panel Pesanan Sticky -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- ── KOLOM KIRI (Menu & Pencarian) ────────────────────────── -->
    <div class="lg:col-span-7 xl:col-span-8 space-y-4 min-w-0">

      <!-- Kolom Pencarian Menu (Debounce 300 ms) -->
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
          <?= icon('search', 'h-4 w-4') ?>
        </span>
        <input
          type="search"
          id="pos-search-input"
          placeholder="Cari makanan atau minuman..."
          class="h-11 w-full rounded-full bg-surface pl-10 pr-10 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
          autocomplete="off"
        >
        <button
          type="button"
          id="pos-search-clear"
          class="hidden absolute inset-y-0 right-0 flex items-center pr-3 text-subtle hover:text-ink cursor-pointer"
          title="Hapus pencarian"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <!-- Baris Chip Kategori (Scroll horizontal tanpa scrollbar) -->
      <div class="overflow-x-auto no-scrollbar py-1">
        <div id="pos-category-chips" class="flex items-center gap-2">
          <button
            type="button"
            class="pos-cat-chip h-9 px-4 rounded-full text-xs font-semibold whitespace-nowrap transition-colors bg-black text-white cursor-pointer"
            data-category-id=""
          >
            Semua
          </button>
          <!-- Chip kategori lainnya dimuat secara dinamis oleh pos.js -->
        </div>
      </div>

      <!-- State Loading (Skeleton Loader) -->
      <div id="pos-menu-loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        <?php for ($i = 0; $i < 6; $i++): ?>
          <div class="h-28 rounded-xl border border-line bg-surface/50 p-3 flex justify-between gap-3 animate-pulse">
            <div class="flex-1 space-y-2">
              <div class="h-4 w-28 bg-line rounded"></div>
              <div class="h-3 w-16 bg-line rounded"></div>
              <div class="h-3 w-36 bg-line/60 rounded"></div>
            </div>
            <div class="w-20 h-20 bg-line rounded-lg shrink-0"></div>
          </div>
        <?php endfor; ?>
      </div>

      <!-- State Error -->
      <div id="pos-menu-error" class="hidden py-16 text-center rounded-xl border border-line bg-white">
        <p class="text-sm font-medium text-danger" id="pos-menu-error-message">Gagal memuat katalog menu.</p>
        <button
          type="button"
          id="pos-btn-retry-menu"
          class="mt-3 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
        >
          Coba Lagi
        </button>
      </div>

      <!-- State Kosong -->
      <div id="pos-menu-empty" class="hidden py-16 text-center rounded-xl border border-line bg-white">
        <div class="mx-auto w-12 h-12 rounded-full bg-surface flex items-center justify-center text-subtle mb-3">
          <?= icon('search', 'h-6 w-6') ?>
        </div>
        <h3 class="text-sm font-bold text-ink">Menu tidak ditemukan</h3>
        <p class="mt-1 text-xs text-subtle" id="pos-menu-empty-message">Coba ubah kata kunci pencarian atau kategori lain.</p>
        <button
          type="button"
          id="pos-btn-reset-filter"
          class="mt-4 h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
        >
          Tampilkan Semua
        </button>
      </div>

      <!-- Grid Menu Ringkas (Klik menambah 1 item) -->
      <div id="pos-menu-grid" class="hidden grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        <!-- Kartu menu dirender secara dinamis oleh pos.js -->
      </div>

    </div>

    <!-- ── KOLOM KANAN (Panel Pesanan Sticky) ────────────────────── -->
    <div class="lg:col-span-5 xl:col-span-4 sticky top-20">
      <div id="pos-order-panel" class="border border-line rounded-xl bg-white shadow-sm flex flex-col overflow-hidden">

        <!-- Header Panel Pesanan -->
        <div class="px-5 py-4 border-b border-line flex items-center justify-between shrink-0 bg-white">
          <div class="flex items-center gap-2">
            <h2 class="text-base font-bold text-ink">Pesanan</h2>
            <span id="pos-items-count-badge" class="px-2 py-0.5 rounded-full bg-surface text-ink text-[11px] font-bold">0 item</span>
          </div>
          <button
            type="button"
            id="pos-btn-clear-cart"
            class="hidden text-xs font-medium text-subtle hover:text-danger transition-colors cursor-pointer"
          >
            Kosongkan
          </button>
        </div>

        <!-- Form Pelanggan & Detail Pesanan -->
        <div class="px-5 py-4 border-b border-line space-y-3.5 shrink-0 bg-white">

          <!-- Input Nama Pelanggan -->
          <div>
            <label for="pos-customer-name" class="block text-xs font-medium text-subtle mb-1">
              Nama Pelanggan <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              id="pos-customer-name"
              placeholder="Mis. Budi"
              maxlength="50"
              autocomplete="off"
              class="h-10 w-full rounded-lg bg-surface px-3 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            >
            <p id="pos-error-name" class="hidden text-xs text-danger mt-1"></p>
          </div>

          <!-- Tipe Pesanan: Toggle Pil Segmented -->
          <div>
            <label class="block text-xs font-medium text-subtle mb-1">Tipe Pesanan</label>
            <div class="rounded-xl bg-surface p-1 flex gap-1">
              <button
                type="button"
                id="pos-type-dine-in"
                data-type="dine_in"
                class="pos-type-btn flex-1 h-9 rounded-lg text-xs font-medium transition-all cursor-pointer bg-white text-ink shadow-sm"
              >
                Makan di tempat
              </button>
              <button
                type="button"
                id="pos-type-takeaway"
                data-type="takeaway"
                class="pos-type-btn flex-1 h-9 rounded-lg text-xs font-medium transition-all cursor-pointer text-subtle hover:text-ink"
              >
                Bawa pulang
              </button>
            </div>
          </div>

          <!-- Nomor Meja (Hanya muncul jika makan di tempat) -->
          <div id="pos-table-group">
            <label for="pos-table-number" class="block text-xs font-medium text-subtle mb-1">
              Nomor Meja <span class="text-danger">*</span>
            </label>
            <input
              type="number"
              id="pos-table-number"
              min="1"
              max="30"
              placeholder="1 - 30"
              class="h-10 w-full rounded-lg bg-surface px-3 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            >
            <p id="pos-error-table" class="hidden text-xs text-danger mt-1"></p>
          </div>

          <!-- Catatan Pesanan (Opsional) -->
          <div>
            <label for="pos-notes" class="block text-xs font-medium text-subtle mb-1">
              Catatan <span class="text-[11px] text-subtle font-normal">(opsional)</span>
            </label>
            <input
              type="text"
              id="pos-notes"
              placeholder="Mis. Sedikit es, tanpa sedotan"
              maxlength="255"
              autocomplete="off"
              class="h-10 w-full rounded-lg bg-surface px-3 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            >
            <p id="pos-error-notes" class="hidden text-xs text-danger mt-1"></p>
          </div>

        </div>

        <!-- Daftar Item Terpilih (Scrollable) -->
        <div class="px-5 py-4 flex-1">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-ink uppercase tracking-wider">Item Terpilih</span>
          </div>

          <!-- State Kosong Keranjang -->
          <div id="pos-cart-empty" class="py-8 text-center text-subtle">
            <div class="mx-auto w-10 h-10 rounded-full bg-surface flex items-center justify-center text-subtle mb-2">
              <?= icon('cart', 'h-5 w-5') ?>
            </div>
            <p class="text-xs font-medium text-ink">Keranjang kosong</p>
            <p class="text-[11px] text-subtle mt-0.5">Klik menu di sebelah kiri untuk menambah pesanan.</p>
          </div>

          <!-- Kontainer Item -->
          <div id="pos-cart-items" class="space-y-3 max-h-64 overflow-y-auto pr-1">
            <!-- Dirender secara dinamis oleh pos.js -->
          </div>
        </div>

        <!-- Footer Total & Aksi Simpan / Simpan & Bayar -->
        <div class="px-5 py-4 border-t border-line space-y-4 shrink-0 bg-white">
          <!-- Total -->
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-subtle">Total Pesanan</span>
            <span id="pos-total-amount" class="text-2xl font-bold text-ink tabular-nums tracking-tight">Rp 0</span>
          </div>

          <!-- Tombol Aksi Pil -->
          <div class="grid grid-cols-2 gap-2">
            <!-- Tombol Simpan (Sekunder) -->
            <button
              type="button"
              id="pos-btn-save"
              class="h-12 rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-center"
              disabled
            >
              Simpan
            </button>

            <!-- Tombol Simpan & Bayar (Utama) -->
            <button
              type="button"
              id="pos-btn-save-pay"
              class="h-12 rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-center"
              disabled
            >
              Simpan &amp; Bayar
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>

</div>
