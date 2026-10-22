<?php
declare(strict_types=1);

/**
 * Layout halaman publik / pelanggan (Gaya Uber Eats).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-white text-ink min-h-screen flex flex-col font-sans antialiased">
  <?php require APP_PATH . '/views/partials/navbar.php'; ?>

  <!-- Konten Utama (Maksimal 6xl sesuai prd.md §12.3) -->
  <main class="flex-1 mx-auto max-w-6xl w-full p-4 sm:p-6">
    <?= $content ?? '' ?>
  </main>

  <!-- Footer Publik Bersih -->
  <footer class="border-t border-line bg-white py-8 mt-auto">
    <div class="mx-auto max-w-6xl px-4 text-center text-xs text-subtle">
      &copy; <?= date('Y') ?> FoodEase — Sistem Pemesanan & Kasir Kafe
    </div>
  </footer>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js')) ?>"></script>
  <script src="<?= e(asset('js/core/cart-store.js')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
