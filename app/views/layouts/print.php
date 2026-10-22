<?php
declare(strict_types=1);

/**
 * Layout halaman cetak struk (Kosong / Minimal untuk cetak struk thermal 80 mm).
 * Variabel yang didukung: $title, $content.
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
</body>
</html>
