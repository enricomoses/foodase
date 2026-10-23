<?php
declare(strict_types=1);

/**
 * View formulir login pengguna (Gaya Uber Eats).
 * Ditampilkan di dalam layouts/auth.php.
 */
?>
<?php
$flashError = \App\Core\Session::get('auth_flash_error');
if ($flashError) {
    \App\Core\Session::forget('auth_flash_error');
}
?>
<div class="space-y-6">
  <!-- Tautan Kembali ke Beranda / Menu -->
  <div>
    <a
      href="<?= e(url()) ?>"
      class="btn-cancel-login inline-flex items-center gap-1.5 text-xs font-medium text-subtle hover:text-ink transition-colors cursor-pointer"
    >
      <?= icon('arrow-left', 'h-4 w-4') ?>
      <span>Kembali ke Beranda</span>
    </a>
  </div>

  <!-- Header / Wordmark Identitas -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Masuk</h1>
    <p class="mt-1 text-sm text-subtle">Gunakan akun staf atau administrator kafe Anda.</p>
  </div>

  <?php if ($flashError): ?>
    <div class="rounded-xl bg-danger-light p-3.5 text-xs font-medium text-danger" role="alert">
      <div class="flex items-start gap-2.5">
        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-danger"></span>
        <span class="leading-snug"><?= e($flashError) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Alert Error Global (Kredensial salah / Throttle) -->
  <div id="login-alert" class="hidden rounded-xl bg-danger-light p-3.5 text-xs font-medium text-danger" role="alert">
    <div class="flex items-start gap-2.5">
      <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-danger"></span>
      <span id="login-alert-message" class="leading-snug"></span>
    </div>
  </div>

  <!-- Form Login -->
  <form id="form-login" action="<?= e(url('api/auth/login.php')) ?>" method="POST" novalidate autocomplete="off" class="space-y-4">
    <?= csrf_field() ?>

    <!-- Pilihan Masuk Sebagai (Role Selector: Semua, Kasir, Admin) -->
    <div>
      <label class="block text-xs font-medium text-subtle mb-1.5">Masuk Sebagai</label>
      <input type="hidden" name="role" id="login-role" value="customer">
      <div class="grid grid-cols-3 gap-1.5 p-1 rounded-xl bg-surface border border-line" id="role-pill-group">
        <button
          type="button"
          class="role-btn h-9 rounded-lg text-xs font-semibold transition-all bg-black text-white shadow-sm cursor-pointer"
          data-role="customer"
        >
          Customer
        </button>
        <button
          type="button"
          class="role-btn h-9 rounded-lg text-xs font-semibold text-subtle hover:text-ink transition-all cursor-pointer"
          data-role="cashier"
        >
          Kasir
        </button>
        <button
          type="button"
          class="role-btn h-9 rounded-lg text-xs font-semibold text-subtle hover:text-ink transition-all cursor-pointer"
          data-role="admin"
        >
          Admin
        </button>
      </div>
    </div>

    <!-- Username -->
    <div>
      <label for="username" class="block text-xs font-medium text-subtle mb-1.5">Username</label>
      <input
        type="text"
        id="username"
        name="username"
        autocomplete="off"
        autofocus
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="Masukkan username"
      >
      <p id="error-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Password Container -->
    <div>
      <label for="password" class="block text-xs font-medium text-subtle mb-1.5">Password</label>
      <div class="relative">
        <input
          type="password"
          id="password"
          name="password"
          autocomplete="new-password"
          class="h-12 w-full rounded-lg bg-surface px-4 pr-11 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
          placeholder="Masukkan password"
        >
        <button
          type="button"
          class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none"
          data-target="#password"
          title="Tampilkan / Sembunyikan Password"
        >
          <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
          <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
        </button>
      </div>

      <!-- Posisi Tautan Lupa Password di Bawah Container Password (Permintaan Pengguna) -->
      <div class="flex justify-end mt-2">
        <button
          type="button"
          id="btn-open-forgot"
          class="text-xs font-medium text-subtle hover:text-ink underline cursor-pointer"
        >
          Lupa password?
        </button>
      </div>
      <p id="error-password" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Tombol Submit Pil Hitam Utama -->
    <div class="pt-2">
      <button
        type="submit"
        id="btn-login"
        class="h-12 w-full rounded-full bg-black px-6 text-sm font-medium text-white shadow-sm hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 transition-colors cursor-pointer"
      >
        Masuk
      </button>
    </div>
  </form>

  <!-- Bagian Buat Akun Baru -->
  <div class="border-t border-line pt-4 text-center">
    <p class="text-xs text-subtle">
      Belum memiliki akun staf?
      <button
        type="button"
        id="btn-open-register"
        class="font-semibold text-ink underline hover:opacity-80 transition-opacity ml-1 cursor-pointer"
      >
        Buat akun baru
      </button>
    </p>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL BUAT AKUN (CREATE ACCOUNT)                         -->
<!-- ======================================================== -->
<div id="modal-register" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <div class="pb-4 border-b border-line">
        <h3 class="text-xl font-bold tracking-tight text-ink">Buat Akun Baru</h3>
        <p class="text-xs text-subtle mt-0.5">Daftarkan akun kasir atau staf kafe baru.</p>
      </div>

      <form id="form-register" action="<?= e(url('api/auth/register.php')) ?>" method="POST" novalidate autocomplete="off" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <div id="register-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="register-alert-message"></span>
        </div>

        <!-- Nama Lengkap -->
        <div>
          <label for="reg-name" class="block text-xs font-medium text-subtle mb-1.5">Nama Lengkap <span class="text-danger">*</span></label>
          <input
            type="text"
            id="reg-name"
            name="name"
            required
            maxlength="100"
            autocomplete="off"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: Rian Prasetya"
          >
          <p id="error-reg-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Username -->
        <div>
          <label for="reg-username" class="block text-xs font-medium text-subtle mb-1.5">Username <span class="text-danger">*</span></label>
          <input
            type="text"
            id="reg-username"
            name="username"
            required
            maxlength="30"
            autocomplete="off"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Contoh: rian_kasir"
          >
          <p class="mt-1 text-[11px] text-subtle">4-30 karakter (huruf, angka, _).</p>
          <p id="error-reg-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Peran / Role Akun -->
        <div>
          <label class="block text-xs font-medium text-subtle mb-1.5">Peran Akun <span class="text-danger">*</span></label>
          <input type="hidden" name="role" id="reg-role" value="cashier">
          <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-surface border border-line" id="reg-role-group">
            <button
              type="button"
              class="reg-role-btn h-9 rounded-lg text-xs font-semibold transition-all bg-black text-white shadow-sm cursor-pointer"
              data-role="cashier"
            >
              Kasir
            </button>
            <button
              type="button"
              class="reg-role-btn h-9 rounded-lg text-xs font-semibold text-subtle hover:text-ink transition-all cursor-pointer"
              data-role="admin"
            >
              Administrator
            </button>
          </div>
        </div>

        <!-- Password Baru -->
        <div>
          <label for="reg-password" class="block text-xs font-medium text-subtle mb-1.5">Password <span class="text-danger">*</span></label>
          <div class="relative">
            <input
              type="password"
              id="reg-password"
              name="password"
              required
              autocomplete="new-password"
              class="h-12 w-full rounded-lg bg-surface px-4 pr-11 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
              placeholder="Masukkan password kuat"
            >
            <button
              type="button"
              class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none"
              data-target="#reg-password"
              title="Tampilkan / Sembunyikan Password"
            >
              <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
              <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
            </button>
          </div>

          <!-- Kriteria Checklist -->
          <div class="mt-2.5 rounded-lg bg-surface p-3 border border-line space-y-1.5 text-xs">
            <div class="flex items-center justify-between text-[11px]">
              <span class="font-medium text-subtle">Kekuatan:</span>
              <span id="reg-strength-label" class="font-semibold text-subtle">-</span>
            </div>
            <div class="grid grid-cols-4 gap-1.5 h-1.5">
              <div id="reg-bar-1" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="reg-bar-2" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="reg-bar-3" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="reg-bar-4" class="h-full rounded-full bg-line transition-colors"></div>
            </div>
            <ul class="pt-1.5 space-y-1 text-[11px]">
              <li id="reg-req-length" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 8 karakter</span>
              </li>
              <li id="reg-req-uppercase" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 huruf besar (A-Z)</span>
              </li>
              <li id="reg-req-number" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 angka (0-9)</span>
              </li>
              <li id="reg-req-special" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 karakter khusus (!@#$...)</span>
              </li>
            </ul>
          </div>
          <p id="error-reg-password" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2.5">
          <button
            type="button"
            class="btn-back-to-login text-xs font-semibold text-ink underline hover:opacity-80 transition-opacity text-left cursor-pointer"
          >
            Sudah punya akun? Masuk
          </button>
          <div class="flex items-center gap-2 justify-end">
            <button
              type="button"
              class="h-11 px-5 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
              data-modal-dismiss
            >
              Batal
            </button>
            <button
              type="submit"
              id="btn-submit-register"
              class="h-11 px-6 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
            >
              Daftar Sekarang
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL LUPA PASSWORD (FORGOT / RESET PASSWORD)             -->
<!-- ======================================================== -->
<div id="modal-forgot" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-dismiss></div>

  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[92vh] overflow-y-auto">
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <div class="pb-4 border-b border-line">
        <h3 class="text-xl font-bold tracking-tight text-ink">Reset Password</h3>
        <p class="text-xs text-subtle mt-0.5">Atur ulang password untuk akun Anda.</p>
      </div>

      <form id="form-forgot" action="<?= e(url('api/auth/forgot-password.php')) ?>" method="POST" novalidate autocomplete="off" class="mt-4 space-y-4">
        <?= csrf_field() ?>

        <div id="forgot-alert" class="hidden rounded-xl bg-danger-light p-3 text-xs font-medium text-danger">
          <span id="forgot-alert-message"></span>
        </div>

        <!-- Username Akun -->
        <div>
          <label for="forgot-username" class="block text-xs font-medium text-subtle mb-1.5">Username Akun <span class="text-danger">*</span></label>
          <input
            type="text"
            id="forgot-username"
            name="username"
            required
            autocomplete="off"
            class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
            placeholder="Masukkan username akun Anda"
          >
          <p id="error-forgot-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <!-- Password Baru -->
        <div>
          <label for="forgot-password" class="block text-xs font-medium text-subtle mb-1.5">Password Baru <span class="text-danger">*</span></label>
          <div class="relative">
            <input
              type="password"
              id="forgot-password"
              name="password"
              required
              autocomplete="new-password"
              class="h-12 w-full rounded-lg bg-surface px-4 pr-11 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
              placeholder="Masukkan password baru"
            >
            <button
              type="button"
              class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none"
              data-target="#forgot-password"
              title="Tampilkan / Sembunyikan Password"
            >
              <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
              <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
            </button>
          </div>

          <!-- Kriteria Checklist Password Baru -->
          <div class="mt-2.5 rounded-lg bg-surface p-3 border border-line space-y-1.5 text-xs">
            <div class="flex items-center justify-between text-[11px]">
              <span class="font-medium text-subtle">Kekuatan:</span>
              <span id="forgot-strength-label" class="font-semibold text-subtle">-</span>
            </div>
            <div class="grid grid-cols-4 gap-1.5 h-1.5">
              <div id="forgot-bar-1" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="forgot-bar-2" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="forgot-bar-3" class="h-full rounded-full bg-line transition-colors"></div>
              <div id="forgot-bar-4" class="h-full rounded-full bg-line transition-colors"></div>
            </div>
            <ul class="pt-1.5 space-y-1 text-[11px]">
              <li id="forgot-req-length" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 8 karakter</span>
              </li>
              <li id="forgot-req-uppercase" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 huruf besar (A-Z)</span>
              </li>
              <li id="forgot-req-number" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 angka (0-9)</span>
              </li>
              <li id="forgot-req-special" class="flex items-center gap-1.5 text-subtle">
                <span class="req-icon flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-line text-subtle">&bull;</span>
                <span>Minimal 1 karakter khusus (!@#$...)</span>
              </li>
            </ul>
          </div>
          <p id="error-forgot-password" class="hidden mt-1 text-xs font-medium text-danger"></p>
        </div>

        <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2.5">
          <button
            type="button"
            class="btn-back-to-login text-xs font-semibold text-ink underline hover:opacity-80 transition-opacity text-left cursor-pointer"
          >
            &larr; Kembali ke Login
          </button>
          <div class="flex items-center gap-2 justify-end">
            <button
              type="button"
              class="h-11 px-5 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
              data-modal-dismiss
            >
              Batal
            </button>
            <button
              type="submit"
              id="btn-submit-forgot"
              class="h-11 px-6 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors cursor-pointer"
            >
              Perbarui Password
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
