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
<base href="<?= e(rtrim(url(), '/')) ?>/">
<title><?= $pageTitle ?></title>

<!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ink: '#000000',
          surface: '#F6F6F6',
          line: '#E2E2E2',
          subtle: '#545454',
          accent: {
            DEFAULT: '#06C167',
            dark: '#048848',
            light: '#E6F8EF',
          },
          danger: {
            DEFAULT: '#E11900',
            light: '#FDECEA',
          },
        },
      },
    },
  };
</script>

<!-- CSS Kustom Aplikasi -->
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
