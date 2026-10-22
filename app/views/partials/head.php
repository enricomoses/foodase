<?php
declare(strict_types=1);

use App\Core\Config;

$pageTitle = isset($title) && $title !== ''
    ? e($title) . ' — ' . e((string) Config::get('app.name', 'FoodEase'))
    : e((string) Config::get('app.name', 'FoodEase'));
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= $pageTitle ?></title>

<!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            DEFAULT: '#b45309',
            dark: '#92400e',
            light: '#fef3c7',
          }
        }
      }
    }
  };
</script>

<!-- CSS Kustom Aplikasi -->
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
