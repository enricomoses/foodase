<?php
declare(strict_types=1);

$cssFile = PUBLIC_PATH . '/assets/css/receipt.css';
$cssVer  = is_file($cssFile) ? (string) filemtime($cssFile) : '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Cetak Struk') ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/receipt.css')) ?>?v=<?= e($cssVer) ?>">
</head>
<body>
  <?= $content ?? '' ?>
  <script src="<?= e(asset('js/pages/receipt.js')) ?>"></script>
</body>
</html>
