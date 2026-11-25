<?php
declare(strict_types=1);

/**
 * Layout halaman publik / pelanggan (Gaya Uber Eats).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="<?= !empty($immersive) ? 'page-immersive' : 'bg-white' ?> text-ink min-h-screen flex flex-col font-sans antialiased">
  <?php if (empty($immersive)): ?>
    <?php require APP_PATH . '/views/partials/navbar.php'; ?>
  <?php endif; ?>

  <!-- Konten Utama (Layar Penuh / Full Screen Responsif) -->
  <main class="<?= !empty($immersive) ? 'p-0 w-full' : 'py-4 sm:py-6 mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20' ?> flex-1">
    <?= $content ?? '' ?>
  </main>

  <?php if (empty($immersive)): ?>
  <!-- Footer Publik Bersih (Layar Penuh / Full Screen) -->
  <footer class="border-t border-line bg-white py-8 mt-auto">
    <div class="mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 text-center text-xs text-subtle">
      &copy; <?= date('Y') ?> Nola — <?= e(__('footer_copyright')) ?>
    </div>
  </footer>
  <?php endif; ?>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js?v=20')) ?>"></script>
  <script src="<?= e(asset('js/core/i18n.js?v=20')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/cart-store.js?v=16')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
