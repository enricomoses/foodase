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
  <form id="form-login" action="<?= e(url('api/auth/login.php')) ?>" method="POST" novalidate class="space-y-4">
    <?= csrf_field() ?>

    <!-- Username -->
    <div>
      <label for="username" class="block text-xs font-medium text-subtle mb-1.5">Username</label>
      <input
        type="text"
        id="username"
        name="username"
        autocomplete="username"
        autofocus
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="Masukkan username"
      >
      <p id="error-username" class="hidden mt-1 text-xs font-medium text-danger"></p>
    </div>

    <!-- Password -->
    <div>
      <label for="password" class="block text-xs font-medium text-subtle mb-1.5">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        autocomplete="current-password"
        class="h-12 w-full rounded-lg bg-surface px-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        placeholder="Masukkan password"
      >
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
</div>
