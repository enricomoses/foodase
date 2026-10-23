<?php
declare(strict_types=1);

/**
 * View formulir pendaftaran pengguna baru (Gaya Uber Eats).
 * Ditampilkan di dalam layouts/auth.php.
 */
?>
<div class="space-y-6">
  <!-- Tautan Kembali ke Beranda / Menu -->
  <div>
    <a
      href="<?= e(url()) ?>"
      class="inline-flex items-center gap-1.5 text-xs font-medium text-subtle hover:text-ink transition-colors"
    >
      <?= icon('arrow-left', 'h-4 w-4') ?>
      <span>Kembali ke Beranda</span>
    </a>
  </div>

  <!-- Header / Wordmark Identitas -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Buat Akun Baru</h1>
    <p class="mt-1 text-sm text-subtle">Daftarkan akun kasir atau staf administrator kafe baru.</p>
  </div>

  <!-- Alert Error Global -->
  <div id="register-page-alert" class="hidden rounded-xl bg-danger-light p-3.5 text-xs font-medium text-danger" role="alert">
    <div class="flex items-start gap-2.5">
      <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-danger"></span>
      <span id="register-page-alert-message" class="leading-snug"></span>
    </div>
  </div>

  <!-- Form Pendaftaran -->
  <form id="form-register-page" action="<?= e(url('api/auth/register.php')) ?>" method="POST" novalidate autocomplete="off" class="space-y-4">
    <?= csrf_field() ?>

    <!-- Nama Lengkap -->
    <div>
      <label for="page-reg-name" class="block text-xs font-medium text-subtle mb-1.5">Nama Lengkap <span class="text-danger">*</span></label>
      <input
        type="text"
        id="page-reg-name"
        name="name"
        required
        maxlength="100"
        autocomplete="off"
        autofocus
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="Contoh: Rian Prasetya"
      >
      <p id="error-page-reg-name" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Username -->
    <div>
      <label for="page-reg-username" class="block text-xs font-medium text-subtle mb-1.5">Username <span class="text-danger">*</span></label>
      <input
        type="text"
        id="page-reg-username"
        name="username"
        required
        maxlength="30"
        autocomplete="off"
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="Contoh: rian_kasir"
      >
      <p class="mt-1 text-[11px] text-subtle">4-30 karakter (huruf, angka, _).</p>
      <p id="error-page-reg-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Peran / Role Akun -->
    <div>
      <label class="block text-xs font-medium text-subtle mb-1.5">Peran Akun <span class="text-danger">*</span></label>
      <input type="hidden" name="role" id="page-reg-role" value="cashier">
      <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-surface border border-line" id="page-reg-role-group">
        <button
          type="button"
          class="page-reg-role-btn h-9 rounded-lg text-xs font-semibold transition-all bg-black text-white shadow-sm cursor-pointer"
          data-role="cashier"
        >
          Kasir
        </button>
        <button
          type="button"
          class="page-reg-role-btn h-9 rounded-lg text-xs font-semibold text-subtle hover:text-ink transition-all cursor-pointer"
          data-role="admin"
        >
          Administrator
        </button>
      </div>
    </div>

    <!-- Password Baru -->
    <div>
      <label for="page-reg-password" class="block text-xs font-medium text-subtle mb-1.5">Password <span class="text-danger">*</span></label>
      <div class="relative">
        <input
          type="password"
          id="page-reg-password"
          name="password"
          required
          autocomplete="new-password"
          class="h-12 w-full rounded-lg bg-surface px-4 pr-11 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
          placeholder="Masukkan password kuat"
        >
        <button
          type="button"
          class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none cursor-pointer"
          data-target="#page-reg-password"
          title="Tampilkan / Sembunyikan Password"
        >
          <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
          <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
        </button>
      </div>

      <!-- Kriteria Checklist Interaktif -->
      <div class="mt-2.5 rounded-lg bg-surface p-3.5 border border-line space-y-2 text-xs">
        <div class="flex items-center justify-between text-[11px]">
          <span class="font-medium text-subtle">Kekuatan Password:</span>
          <span id="page-reg-strength-label" class="font-bold text-subtle transition-colors">-</span>
        </div>
        <div class="grid grid-cols-4 gap-1.5 h-1.5">
          <div id="page-reg-bar-1" class="h-full rounded-full bg-line transition-all duration-300"></div>
          <div id="page-reg-bar-2" class="h-full rounded-full bg-line transition-all duration-300"></div>
          <div id="page-reg-bar-3" class="h-full rounded-full bg-line transition-all duration-300"></div>
          <div id="page-reg-bar-4" class="h-full rounded-full bg-line transition-all duration-300"></div>
        </div>
        <ul class="pt-1.5 space-y-1.5 text-[11px]">
          <li id="page-reg-req-length" class="flex items-center gap-2 text-subtle transition-colors">
            <span class="req-icon flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-line text-subtle text-[10px] transition-colors">&bull;</span>
            <span>Minimal 8 karakter</span>
          </li>
          <li id="page-reg-req-uppercase" class="flex items-center gap-2 text-subtle transition-colors">
            <span class="req-icon flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-line text-subtle text-[10px] transition-colors">&bull;</span>
            <span>Minimal 1 huruf besar (A-Z)</span>
          </li>
          <li id="page-reg-req-number" class="flex items-center gap-2 text-subtle transition-colors">
            <span class="req-icon flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-line text-subtle text-[10px] transition-colors">&bull;</span>
            <span>Minimal 1 angka (0-9)</span>
          </li>
          <li id="page-reg-req-special" class="flex items-center gap-2 text-subtle transition-colors">
            <span class="req-icon flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-line text-subtle text-[10px] transition-colors">&bull;</span>
            <span>Minimal 1 karakter khusus (!@#$...)</span>
          </li>
        </ul>
      </div>
      <p id="error-page-reg-password" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Tombol Submit Pil Hitam Utama -->
    <div class="pt-2">
      <button
        type="submit"
        id="btn-page-submit-register"
        class="h-12 w-full rounded-full bg-black px-6 text-sm font-medium text-white shadow-sm hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 transition-colors cursor-pointer"
      >
        Daftar Sekarang
      </button>
    </div>
  </form>

  <!-- Bagian Kembali ke Login -->
  <div class="border-t border-line pt-4 text-center">
    <p class="text-xs text-subtle">
      Sudah memiliki akun staf?
      <a
        href="<?= e(url('login.php')) ?>"
        class="font-semibold text-ink underline hover:opacity-80 transition-opacity ml-1"
      >
        Masuk ke akun Anda
      </a>
    </p>
  </div>
</div>
