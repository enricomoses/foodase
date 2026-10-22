<?php
declare(strict_types=1);

/**
 * Layout halaman publik / pelanggan.
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col font-sans">
  <?php require APP_PATH . '/views/partials/navbar.php'; ?>

  <!-- Konten Utama -->
  <main class="flex-1 mx-auto max-w-5xl w-full p-4 sm:p-6">
    <?= $content ?? '' ?>
  </main>

  <!-- Footer Publik -->
  <footer class="border-t border-stone-200 bg-white py-6 mt-auto">
    <div class="mx-auto max-w-5xl px-4 text-center text-xs text-stone-500">
      &copy; <?= date('Y') ?> FoodEase — Sistem Pemesanan & Kasir Kafe
    </div>
  </footer>

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
