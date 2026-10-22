<?php
declare(strict_types=1);

/**
 * Layout halaman autentikasi (kartu tengah).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex items-center justify-center p-4 font-sans">
  <!-- Kartu Tengah -->
  <div class="w-full max-w-sm rounded-md border border-stone-200 bg-white p-6 shadow-sm">
    <?= $content ?? '' ?>
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
