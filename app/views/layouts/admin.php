<?php
declare(strict_types=1);

use App\Core\Auth;

/**
 * Layout area Administrator.
 * Variabel yang didukung: $title, $content, $activeMenu, $scripts (array).
 */
$authUser = Auth::user();
$userName = $authUser['name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex font-sans">
  <!-- Sidebar Kiri Sempit -->
  <?php require APP_PATH . '/views/partials/sidebar.php'; ?>

  <!-- Area Konten Utama & Header -->
  <div class="flex-1 flex flex-col min-w-0">
    <!-- Header Atas -->
    <header class="h-14 border-b border-stone-200 bg-white flex items-center justify-between px-6 sticky top-0 z-20">
      <div class="flex items-center gap-2">
        <h1 class="text-base font-semibold text-stone-900"><?= e($title ?? 'Panel Admin') ?></h1>
      </div>

      <div class="flex items-center gap-4">
        <!-- Info Pengguna -->
        <span class="text-sm font-medium text-stone-700"><?= e($userName) ?></span>

        <!-- Tombol Keluar -->
        <form action="<?= e(url('api/auth/logout.php')) ?>" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
          <?= csrf_field() ?>
          <button type="submit" class="rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900">
            Keluar
          </button>
        </form>
      </div>
    </header>

    <!-- Konten Halaman -->
    <main class="flex-1 p-6 overflow-y-auto">
      <?= $content ?? '' ?>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
