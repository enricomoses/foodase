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

<!-- LANGKAH 1: Cek preferensi tema sebelum render (tanpa kedipan) -->
<script>
  (function () {
    try {
      const stored = localStorage.getItem('foodease_theme');
      if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    } catch (e) {}
  })();
</script>

<!-- LANGKAH 2: Config Tailwind HARUS diset SEBELUM CDN script -->
<script>
  // Tailwind CDN membaca tailwind.config dari window sebelum ia selesai inisialisasi
  // Pastikan ini ada sebelum <script src="cdn.tailwindcss.com">
  tailwind = { config: {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          ink: '#000000',
          surface: '#F6F6F6',
          line: '#E2E2E2',
          subtle: '#545454',
          accent: { DEFAULT: '#06C167', dark: '#048848', light: '#E6F8EF' },
          danger:  { DEFAULT: '#E11900', light: '#FDECEA' },
        },
      },
    },
  }};
</script>

<!-- LANGKAH 3: Load Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- LANGKAH 4: CSS Kustom Aplikasi (SETELAH Tailwind) -->
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
