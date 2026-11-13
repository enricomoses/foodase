<?php
declare(strict_types=1);

/**
 * View Login Portal Staf & Administrator (Gaya Uber Eats).
 * Ditampilkan di dalam layouts/auth.php.
 */
$flashError = \App\Core\Session::get('auth_flash_error');
if ($flashError) {
    \App\Core\Session::forget('auth_flash_error');
}
?>
<div class="space-y-6">
  <!-- Tautan Kembali ke Beranda / Menu Pelanggan -->
  <div class="flex items-center justify-between">
    <a
      href="<?= e(url()) ?>"
      class="inline-flex items-center gap-1.5 text-xs font-medium text-subtle hover:text-ink transition-colors cursor-pointer"
    >
      <?= icon('arrow-left', 'h-4 w-4') ?>
      <span data-i18n="back_to_menu"><?= e(__('back_to_menu')) ?></span>
    </a>
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black text-white dark:bg-white dark:text-black" data-i18n="admin_portal_badge">
      <?= e(__('admin_portal_badge')) ?>
    </span>
  </div>

  <!-- Header / Identitas Portal -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-ink" data-i18n="admin_login_title"><?= e(__('admin_login_title')) ?></h1>
    <p class="mt-1 text-sm text-subtle" data-i18n="admin_login_subtitle"><?= e(__('admin_login_subtitle')) ?></p>
  </div>

  <?php if ($flashError): ?>
    <div class="rounded-xl bg-danger-light p-3.5 text-xs font-medium text-danger" role="alert">
      <div class="flex items-start gap-2.5">
        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-danger"></span>
        <span class="leading-snug"><?= e($flashError) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Alert Error Dinamis (Kredensial salah / Throttle) -->
  <div id="admin-login-alert" class="hidden rounded-xl bg-danger-light p-3.5 text-xs font-medium text-danger" role="alert">
    <div class="flex items-start gap-2.5">
      <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-danger"></span>
      <span id="admin-login-alert-message" class="leading-snug"></span>
    </div>
  </div>

  <!-- Form Login Staf -->
  <form id="form-admin-login" action="<?= e(url('api/auth/login.php')) ?>" method="POST" novalidate autocomplete="off" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="scope" value="staff">

    <!-- Pilihan Peran Staf: Kasir / Administrator -->
    <div>
      <label class="block text-xs font-medium text-subtle mb-1.5" data-i18n="admin_login_role"><?= e(__('admin_login_role')) ?></label>
      <input type="hidden" name="role" id="admin-login-role" value="cashier">
      <div class="grid grid-cols-2 gap-1.5 p-1 rounded-xl bg-surface border border-line" id="staff-role-pill-group">
        <button
          type="button"
          class="staff-role-btn h-9 rounded-lg text-xs font-semibold transition-all bg-black text-white shadow-sm cursor-pointer"
          data-role="cashier"
          data-i18n="register_cashier"
        >
          <?= e(__('register_cashier')) ?>
        </button>
        <button
          type="button"
          class="staff-role-btn h-9 rounded-lg text-xs font-semibold text-subtle hover:text-ink transition-all cursor-pointer"
          data-role="admin"
          data-i18n="register_admin"
        >
          <?= e(__('register_admin')) ?>
        </button>
      </div>
    </div>

    <!-- Username -->
    <div>
      <label for="admin-username" class="block text-xs font-medium text-subtle mb-1.5" data-i18n="login_username"><?= e(__('login_username')) ?></label>
      <input
        type="text"
        id="admin-username"
        name="username"
        autocomplete="off"
        autofocus
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="<?= e(__('login_username')) ?>"
        data-i18n-placeholder="login_username"
      >
      <p id="error-admin-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Password -->
    <div>
      <label for="admin-password" class="block text-xs font-medium text-subtle mb-1.5" data-i18n="login_password"><?= e(__('login_password')) ?></label>
      <div class="relative">
        <input
          type="password"
          id="admin-password"
          name="password"
          autocomplete="current-password"
          class="h-12 w-full rounded-lg bg-surface px-4 pr-11 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
          placeholder="<?= e(__('login_password')) ?>"
          data-i18n-placeholder="login_password"
        >
        <button
          type="button"
          class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none cursor-pointer"
          data-target="#admin-password"
          title="Tampilkan / Sembunyikan Password"
        >
          <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
          <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
        </button>
      </div>
      <p id="error-admin-password" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Tombol Submit Pil Hitam Utama -->
    <div class="pt-2">
      <button
        type="submit"
        id="btn-admin-login"
        class="h-12 w-full rounded-full bg-black text-white dark:bg-white dark:text-black px-6 text-sm font-semibold shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 transition-all cursor-pointer"
        data-i18n="admin_login_btn"
      >
        <?= e(__('admin_login_btn')) ?>
      </button>
    </div>
  </form>

  <!-- Bagian Informasi Tambahan -->
  <div class="border-t border-line pt-4 text-center text-xs">
    <p class="text-[11px] text-subtle">
      Butuh bantuan akun? Hubungi administrator kafe.
    </p>
  </div>
</div>

