<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-white text-ink font-sans antialiased">
  <?php require APP_PATH . '/views/partials/navbar.php'; ?>

  <main class="mx-auto max-w-6xl p-6">
    <h1 class="text-3xl font-bold tracking-tight text-ink">FoodEase</h1>
    <p class="mt-2 text-sm text-subtle">Setup berhasil. Base URL: <?= e(url()) ?></p>
    <button id="btn-test" class="mt-4 h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 transition-colors">
      Tes jQuery
    </button>
    <p id="out" class="mt-3 text-sm text-subtle"></p>
  </main>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js')) ?>"></script>
  <script>
    $('#btn-test').on('click', function () {
      $('#out').text('jQuery bekerja dengan baik.');
      UI.toast('success', 'jQuery dan UI toast berfungsi!');
    });
  </script>
</body>
</html>