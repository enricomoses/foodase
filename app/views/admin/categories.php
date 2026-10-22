<?php
declare(strict_types=1);

/**
 * View kelola kategori untuk administrator (Gaya Uber Eats).
 */
?>
<div class="space-y-6">
  <!-- Baris Judul & Tombol Aksi Tambah -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Kelola Kategori</h1>
      <p class="mt-1 text-sm text-subtle">Atur kategori menu dan urutan tampilannya pada katalog kafe.</p>
    </div>

    <div>
      <button
        type="button"
        id="btn-add-category"
        class="h-11 px-5 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-2 hover:bg-neutral-800 transition-colors shadow-sm cursor-pointer"
      >
        <?= icon('plus', 'h-4 w-4') ?>
        <span>Tambah Kategori</span>
      </button>
    </div>
  </div>

  <!-- Kontainer Tabel Kategori -->
  <div class="rounded-xl border border-line bg-white shadow-sm overflow-hidden">
    <!-- State Loading / Skeleton -->
    <div id="category-loading" class="p-6">
      <div class="space-y-3 animate-pulse">
        <div class="h-10 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
      </div>
    </div>

    <!-- State Error -->
    <div id="category-error" class="hidden p-8 text-center">
      <p class="text-sm font-medium text-danger" id="category-error-message">Gagal memuat daftar kategori.</p>
      <button
        type="button"
        id="btn-retry-load"
        class="mt-3 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
      >
        Coba Lagi
      </button>
    </div>

    <!-- State Kosong -->
    <div id="category-empty" class="hidden py-16 text-center">
      <p class="text-sm font-medium text-subtle">Belum ada kategori yang dibuat.</p>
      <button
        type="button"
        class="btn-trigger-add mt-3 h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
      >
        Tambah Kategori Pertama
      </button>
    </div>

    <!-- Tabel Data -->
    <div id="category-table-wrapper" class="hidden overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-line bg-surface text-xs font-medium text-subtle">
          <tr>
            <th class="py-3 px-5 w-24">Urutan</th>
            <th class="py-3 px-5">Nama Kategori</th>
            <th class="py-3 px-5 w-36 text-center">Jumlah Menu</th>
            <th class="py-3 px-5 w-44 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="category-table-body" class="divide-y divide-line">
          <!-- Diisi secara dinamis oleh admin-categories.js -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form Tambah / Edit Kategori (Bottom Sheet di Mobile) -->
<div id="modal-category-form" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <!-- Latar belakang gelap -->
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <!-- Konten dialog -->
  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[90vh] overflow-y-auto">
      <!-- Handle drag indicator di mobile -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <!-- Header Modal -->
      <div class="flex items-center justify-between pb-4 border-b border-line">
        <h3 id="modal-category-title" class="text-xl font-bold tracking-tight text-ink">Tambah Kategori</h3>
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
      <form id="form-category" novalidate class="mt-4 space-y-4">
        <input type="hidden" id="category-id" name="id" value="">

        <!-- Alert Error Form -->
        <div id="form-category-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="form-category-alert-message"></span>
        </div>

        <!-- Nama Kategori -->
        <div>
          <label for="category-name" class="block text-xs font-medium text-subtle mb-1.5">
            Nama Kategori <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            id="category-name"
            name="name"
            required
            maxlength="60"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: Kopi, Minuman Dingin, Camilan"
          >
          <p id="error-category-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Urutan (Sort Order) -->
        <div>
          <label for="category-sort-order" class="block text-xs font-medium text-subtle mb-1.5">
            Urutan Tampilan
          </label>
          <input
            type="number"
            id="category-sort-order"
            name="sort_order"
            min="0"
            step="1"
            value="0"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="0"
          >
          <p class="mt-1 text-[11px] text-subtle">Kategori dengan angka lebih kecil akan ditampilkan lebih dulu.</p>
          <p id="error-category-sort-order" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
          <button
            type="button"
            class="h-12 px-6 rounded-full bg-surface text-ink font-medium hover:bg-neutral-200 transition-colors"
            data-modal-dismiss
          >
            Batal
          </button>
          <button
            type="submit"
            id="btn-save-category"
            class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
          >
            Simpan Kategori
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
