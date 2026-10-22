<?php
declare(strict_types=1);

/**
 * Layout halaman cetak struk (80 mm receipt / blank print).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Cetak Struk') ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="bg-white text-black p-0 m-0 font-mono text-xs">
  <?= $content ?? '' ?>

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
