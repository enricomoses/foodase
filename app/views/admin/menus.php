<?php
declare(strict_types=1);

/**
 * View kelola menu untuk administrator (FR-MENU-01..06, Gaya Uber Eats §12).
 * Variabel tersedia: $categories (array)
 */
?>
<div class="space-y-6">
  <!-- Baris Judul & Tombol Aksi Tambah -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Kelola Menu</h1>
      <p class="mt-1 text-sm text-subtle">Tambah, ubah, atau hapus menu serta atur ketersediaannya.</p>
    </div>
    <div>
      <button
        type="button"
        id="btn-add-menu"
        class="h-11 px-5 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-2 hover:bg-neutral-800 transition-colors shadow-sm cursor-pointer"
      >
        <?= icon('plus', 'h-4 w-4') ?>
        <span>Tambah Menu</span>
      </button>
    </div>
  </div>

  <!-- Filter: Cari & Kategori -->
  <div class="flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
      <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-subtle">
        <?= icon('search', 'h-4 w-4') ?>
      </span>
      <input
        type="search"
        id="menu-search"
        placeholder="Cari nama menu..."
        autocomplete="off"
        class="h-11 w-full rounded-full bg-surface pl-10 pr-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
      >
    </div>
    <select
      id="menu-filter-category"
      class="h-11 rounded-full bg-surface px-4 pr-8 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors cursor-pointer"
    >
      <option value="">Semua Kategori</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e((string) $cat['id']) ?>"><?= e($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Kontainer Tabel Menu -->
  <div class="rounded-xl border border-line bg-white shadow-sm overflow-hidden">
    <!-- State Loading / Skeleton -->
    <div id="menu-loading" class="p-6">
      <div class="space-y-3 animate-pulse">
        <div class="h-10 rounded-lg bg-surface"></div>
        <div class="h-16 rounded-lg bg-surface"></div>
        <div class="h-16 rounded-lg bg-surface"></div>
        <div class="h-16 rounded-lg bg-surface"></div>
        <div class="h-16 rounded-lg bg-surface"></div>
      </div>
    </div>

    <!-- State Error -->
    <div id="menu-error" class="hidden p-8 text-center">
      <p class="text-sm font-medium text-danger" id="menu-error-message">Gagal memuat daftar menu.</p>
      <button
        type="button"
        id="btn-retry-menu"
        class="mt-3 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
      >
        Coba Lagi
      </button>
    </div>

    <!-- State Kosong -->
    <div id="menu-empty" class="hidden py-16 text-center">
      <p class="text-sm font-medium text-subtle">Belum ada menu yang sesuai.</p>
      <button
        type="button"
        id="btn-empty-add"
        class="mt-3 h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
      >
        Tambah Menu Pertama
      </button>
    </div>

    <!-- Tabel Data -->
    <div id="menu-table-wrapper" class="hidden overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-line bg-surface text-xs font-medium text-subtle">
          <tr>
            <th class="py-3 px-4 w-20">Foto</th>
            <th class="py-3 px-4">Nama Menu</th>
            <th class="py-3 px-4 w-40">Kategori</th>
            <th class="py-3 px-4 w-36 text-right">Harga</th>
            <th class="py-3 px-4 w-28 text-center">Tersedia</th>
            <th class="py-3 px-4 w-44 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="menu-table-body" class="divide-y divide-line">
          <!-- Diisi oleh admin-menus.js -->
        </tbody>
      </table>
    </div>

    <!-- Paginasi -->
    <div id="menu-pagination" class="hidden border-t border-line px-5 py-3 flex items-center justify-between gap-4">
      <span id="menu-pagination-info" class="text-xs text-subtle"></span>
      <div class="flex items-center gap-1.5" id="menu-pagination-btns"></div>
    </div>
  </div>
</div>

<!-- ===== MODAL FORM TAMBAH / EDIT MENU ===== -->
<div id="modal-menu-form" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <!-- Latar belakang -->
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl bg-white shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <!-- Handle drag indicator -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mt-3 mb-2 sm:hidden"></div>

      <!-- Header Modal -->
      <div class="flex items-center justify-between px-6 pt-4 pb-4 border-b border-line">
        <h3 id="modal-menu-title" class="text-xl font-bold tracking-tight text-ink">Tambah Menu</h3>
        <button
          type="button"
          class="h-8 w-8 rounded-full flex items-center justify-center text-subtle hover:bg-surface hover:text-ink transition-colors"
          data-modal-dismiss
          aria-label="Tutup"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <!-- Form -->
      <form id="form-menu" novalidate enctype="multipart/form-data" class="px-6 pt-5 pb-6 space-y-4">
        <input type="hidden" id="menu-id" name="id" value="">

        <!-- Alert Error Global -->
        <div id="form-menu-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="form-menu-alert-message"></span>
        </div>

        <!-- Preview Foto -->
        <div>
          <label class="block text-xs font-medium text-subtle mb-1.5">Foto Menu <span class="text-subtle font-normal">(opsional, JPG/PNG/WebP maks 2 MB)</span></label>
          <div class="flex items-start gap-4">
            <div class="relative w-24 h-24 rounded-xl overflow-hidden border border-line shrink-0 bg-surface">
              <img
                id="menu-img-preview"
                src="<?= e(url('assets/img/placeholder-menu.svg')) ?>"
                alt="Preview foto menu"
                class="w-full h-full object-cover"
              >
            </div>
            <div class="flex-1">
              <label
                for="menu-image"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
              >
                <?= icon('plus', 'h-3.5 w-3.5') ?>
                Pilih Foto
              </label>
              <input
                type="file"
                id="menu-image"
                name="image"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
              >
              <p id="menu-image-name" class="mt-1.5 text-xs text-subtle">Belum ada foto dipilih</p>
              <p id="error-menu-image" class="hidden mt-1 text-xs font-medium text-danger"></p>
            </div>
          </div>
        </div>

        <!-- Nama Menu -->
        <div>
          <label for="menu-name" class="block text-xs font-medium text-subtle mb-1.5">
            Nama Menu <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            id="menu-name"
            name="name"
            maxlength="100"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: Es Kopi Susu"
          >
          <p id="error-menu-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Kategori -->
        <div>
          <label for="menu-category" class="block text-xs font-medium text-subtle mb-1.5">
            Kategori <span class="text-danger">*</span>
          </label>
          <select
            id="menu-category"
            name="category_id"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors cursor-pointer"
          >
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= e((string) $cat['id']) ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p id="error-menu-category" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Harga -->
        <div>
          <label for="menu-price" class="block text-xs font-medium text-subtle mb-1.5">
            Harga (Rp) <span class="text-danger">*</span>
          </label>
          <input
            type="number"
            id="menu-price"
            name="price"
            min="1"
            max="10000000"
            step="500"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: 25000"
          >
          <p id="error-menu-price" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Deskripsi -->
        <div>
          <label for="menu-description" class="block text-xs font-medium text-subtle mb-1.5">
            Deskripsi <span class="text-subtle font-normal">(opsional, maks 500 karakter)</span>
          </label>
          <textarea
            id="menu-description"
            name="description"
            rows="3"
            maxlength="500"
            class="w-full rounded-lg bg-surface px-4 py-3 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors resize-none"
            placeholder="Deskripsi singkat menu ini..."
          ></textarea>
          <p id="error-menu-description" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Status Tersedia -->
        <div class="flex items-center justify-between rounded-lg bg-surface px-4 py-3">
          <div>
            <p class="text-sm font-medium text-ink">Tersedia</p>
            <p class="text-xs text-subtle">Menu bisa dipesan oleh pelanggan</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="menu-available" name="is_available" value="1" checked class="sr-only peer">
            <div class="w-11 h-6 bg-line rounded-full peer peer-checked:bg-black transition-colors"></div>
            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
          </label>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
          <button
            type="button"
            class="h-12 px-6 rounded-full bg-surface text-ink font-medium hover:bg-neutral-200 transition-colors"
            data-modal-dismiss
          >
            Batal
          </button>
          <button
            type="submit"
            id="btn-save-menu"
            class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
          >
            Simpan Menu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
