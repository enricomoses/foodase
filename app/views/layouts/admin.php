<?php
declare(strict_types=1);

use App\Core\Auth;

/**
 * Layout area Administrator (Gaya Uber Eats).
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
<body class="bg-white text-ink min-h-screen flex font-sans antialiased">
  <!-- Sidebar Putih Berborder Kanan -->
  <?php require APP_PATH . '/views/partials/sidebar.php'; ?>

  <!-- Area Konten Utama & Header -->
  <div class="flex-1 flex flex-col min-w-0">
    <!-- Header Atas Sticky -->
    <header class="h-16 border-b border-line bg-white flex items-center justify-between px-6 sticky top-0 z-20">
      <div class="flex items-center gap-2">
        <h1 class="text-lg font-bold tracking-tight text-ink"><?= e($title ?? 'Panel Admin') ?></h1>
      </div>

      <div class="flex items-center gap-4">
        <!-- Info Pengguna -->
        <span class="text-sm font-medium text-ink"><?= e($userName) ?></span>

        <!-- Tombol Keluar (Bentuk Pil Sekunder) -->
        <form action="<?= e(url('logout.php')) ?>" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
          <?= csrf_field() ?>
          <button type="submit" class="h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors">
            Keluar
          </button>
        </form>
      </div>
    </header>

    <!-- Konten Halaman (Maksimal 7xl sesuai prd.md §12.3) -->
    <main class="flex-1 p-6 overflow-y-auto max-w-7xl w-full">
      <?= $content ?? '' ?>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js')) ?>"></script>
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
