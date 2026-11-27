<?php
declare(strict_types=1);

/**
 * View kelola akun kasir untuk administrator (Gaya Uber Eats).
 * FR-USER-01..04, BR-15.
 */
?>
<div class="space-y-6">
  <!-- Baris Judul & Tombol Aksi Tambah -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-ink">Kelola Akun Kasir</h1>
      <p class="mt-1 text-sm text-subtle">Atur akun kasir, kredensial login, dan status akses sistem.</p>
    </div>

    <div>
      <button
        type="button"
        id="btn-add-user"
        class="h-11 px-5 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-2 hover:bg-neutral-800 transition-colors shadow-sm cursor-pointer"
      >
        <?= icon('plus', 'h-4 w-4') ?>
        <span>Tambah Kasir</span>
      </button>
    </div>
  </div>

  <!-- Kontainer Tabel Kasir -->
  <div class="rounded-xl border border-line bg-white shadow-sm overflow-hidden">
    <!-- State Loading / Skeleton -->
    <div id="users-loading" class="p-6">
      <div class="space-y-3 animate-pulse">
        <div class="h-10 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
        <div class="h-12 rounded-lg bg-surface"></div>
      </div>
    </div>

    <!-- State Error -->
    <div id="users-error" class="hidden p-8 text-center">
      <p class="text-sm font-medium text-danger" id="users-error-message">Gagal memuat daftar kasir.</p>
      <button
        type="button"
        id="btn-retry-users"
        class="mt-3 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
      >
        Coba Lagi
      </button>
    </div>

    <!-- State Kosong -->
    <div id="users-empty" class="hidden py-16 text-center">
      <p class="text-sm font-medium text-subtle">Belum ada akun kasir yang terdaftar.</p>
      <button
        type="button"
        id="btn-empty-add-user"
        class="mt-3 h-9 px-4 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
      >
        Tambah Kasir Pertama
      </button>
    </div>

    <!-- Tabel Data -->
    <div id="users-table-wrapper" class="hidden overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-line bg-surface text-xs font-medium text-subtle">
          <tr>
            <th class="py-3 px-5">Nama Lengkap</th>
            <th class="py-3 px-5 w-44">Username</th>
            <th class="py-3 px-5 w-36 text-center">Status</th>
            <th class="py-3 px-5 w-48">Login Terakhir</th>
            <th class="py-3 px-5 w-52 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="users-table-body" class="divide-y divide-line">
          <!-- Diisi secara dinamis oleh admin-users.js -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH KASIR (dengan UI Create Password) -->
<!-- ========================================== -->
<div id="modal-create-user" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <!-- Handle drag indicator di mobile -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <!-- Header Modal -->
      <div class="flex items-center justify-between pb-4 border-b border-line">
        <h3 class="text-xl font-bold tracking-tight text-ink">Tambah Akun Kasir</h3>
        <button
          type="button"
          class="h-8 w-8 rounded-full flex items-center justify-center text-subtle hover:bg-surface hover:text-ink transition-colors"
          data-modal-dismiss
          aria-label="Tutup"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <!-- Form Tambah -->
      <form id="form-create-user" novalidate class="mt-4 space-y-4">
        <!-- Alert Error Form -->
        <div id="create-user-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="create-user-alert-message"></span>
        </div>

        <!-- Nama Lengkap -->
        <div>
          <label for="create-name" class="block text-xs font-medium text-subtle mb-1.5">
            Nama Lengkap <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            id="create-name"
            name="name"
            required
            maxlength="100"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: Budi Santoso"
          >
          <p id="error-create-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Username -->
        <div>
          <label for="create-username" class="block text-xs font-medium text-subtle mb-1.5">
            Username <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            id="create-username"
            name="username"
            required
            maxlength="30"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: budi_kasir"
          >
          <p class="mt-1 text-[11px] text-subtle">4-30 karakter, hanya huruf, angka, dan underscore (_).</p>
          <p id="error-create-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <?= \App\Core\View::partial('password-checklist', ['prefix' => 'create', 'inputId' => 'create-password']) ?>

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
            id="btn-submit-create"
            disabled
            class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Simpan Kasir
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL EDIT KASIR (Nama & Status Aktif)      -->
<!-- ========================================== -->
<div id="modal-edit-user" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <div class="flex items-center justify-between pb-4 border-b border-line">
        <h3 class="text-xl font-bold tracking-tight text-ink">Edit Akun Kasir</h3>
        <button
          type="button"
          class="h-8 w-8 rounded-full flex items-center justify-center text-subtle hover:bg-surface hover:text-ink transition-colors"
          data-modal-dismiss
          aria-label="Tutup"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <form id="form-edit-user" novalidate class="mt-4 space-y-4">
        <input type="hidden" id="edit-id" name="id" value="">

        <div id="edit-user-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="edit-user-alert-message"></span>
        </div>

        <!-- Username (Readonly) -->
        <div>
          <label class="block text-xs font-medium text-subtle mb-1.5">Username</label>
          <input
            type="text"
            id="edit-username"
            disabled
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-subtle border border-line cursor-not-allowed"
          >
        </div>

        <!-- Nama Lengkap -->
        <div>
          <label for="edit-name" class="block text-xs font-medium text-subtle mb-1.5">
            Nama Lengkap <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            id="edit-name"
            name="name"
            required
            maxlength="100"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
          >
          <p id="error-edit-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Status Akun Switch -->
        <div class="pt-2">
          <label class="flex items-center justify-between cursor-pointer p-3 rounded-xl border border-line bg-surface hover:bg-neutral-100 transition-colors">
            <div>
              <p class="text-sm font-medium text-ink">Status Akun Aktif</p>
              <p class="text-xs text-subtle">Izinkan kasir ini login ke dalam sistem.</p>
            </div>
            <div class="relative inline-flex items-center">
              <input type="checkbox" id="edit-is-active" name="is_active" class="sr-only peer">
              <div class="w-11 h-6 bg-danger rounded-full peer peer-checked:bg-accent transition-colors"></div>
              <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
            </div>
          </label>
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
            id="btn-submit-edit"
            class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 transition-colors"
          >
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL RESET PASSWORD (dengan UI Create Password) -->
<!-- ========================================== -->
<div id="modal-reset-password" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <div class="flex items-center justify-between pb-4 border-b border-line">
        <div>
          <h3 class="text-xl font-bold tracking-tight text-ink">Reset Password</h3>
          <p id="reset-target-info" class="text-xs text-subtle mt-0.5"></p>
        </div>
        <button
          type="button"
          class="h-8 w-8 rounded-full flex items-center justify-center text-subtle hover:bg-surface hover:text-ink transition-colors"
          data-modal-dismiss
          aria-label="Tutup"
        >
          <?= icon('close', 'h-4 w-4') ?>
        </button>
      </div>

      <form id="form-reset-password" novalidate class="mt-4 space-y-4">
        <input type="hidden" id="reset-id" name="id" value="">

        <div id="reset-user-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="reset-user-alert-message"></span>
        </div>

        <?= \App\Core\View::partial('password-checklist', ['prefix' => 'reset', 'inputId' => 'reset-password-input']) ?>

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
            id="btn-submit-reset"
            disabled
            class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Reset Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
