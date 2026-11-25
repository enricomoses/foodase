<?php
declare(strict_types=1);

use App\Core\Config;

$appName = (string) Config::get('app.name', 'Nola');
$pageTitle = isset($title) && $title !== ''
    ? e($title) . ' — ' . e($appName)
    : e($appName);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<base href="<?= e(rtrim(url(), '/')) ?>/">
<title><?= $pageTitle ?></title>

<!-- Brand Typography: Plus Jakarta Sans / Inter Tight (headline/UI), DM Sans (body), JetBrains Mono (angka/ID/harga), Playball (script wordmark) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Playball&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- LANGKAH 1: Tema Default Terang / Putih Bersih (Khas Kopi Calf Menu) -->
<script>
  (function () {
    try {
      // Default tema adalah Light (Putih) agar katalog menu selalu bersih dan kontras
      const stored = localStorage.getItem('nola_theme') || localStorage.getItem('kopinusa_theme');
      if (stored === 'dark') {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    } catch (e) {
      document.documentElement.classList.remove('dark');
    }
  })();
</script>

<!-- LANGKAH 2: Config Tailwind HARUS diset SEBELUM CDN script -->
<script>
  tailwind = { config: {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          azure: {
            50: '#ECEEFE',
            100: '#CDD2FC',
            200: '#9AA3F8',
            300: '#5B68F3',
            DEFAULT: '#000EE9',
            500: '#000EE9',
            600: '#0009B8',
            700: '#00078A',
          },
          ember: {
            50: '#FFECEC',
            300: '#FF5C5C',
            DEFAULT: '#FF0000',
            500: '#FF0000',
            700: '#990000',
          },
          n: {
            0: '#FFFFFF',
            25: '#FBFBFA',
            50: '#F5F5F3',
            100: '#EAEAE6',
            200: '#D9D9D3',
            300: '#BCBCB6',
            400: '#93938D',
            500: '#6E6E68',
            600: '#4C4C47',
            700: '#343430',
            800: '#20201D',
            900: '#131311',
          },
          ink: '#131311',
          surface: '#F5F5F3',
          line: '#EAEAE6',
          subtle: '#6E6E68',
          accent: { DEFAULT: '#000EE9', dark: '#0009B8', light: '#ECEEFE' },
          danger:  { DEFAULT: '#FF0000', light: '#FFECEC' },
        },
        fontFamily: {
          sans: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
          ui: ['"Inter Tight"', 'system-ui', '-apple-system', 'sans-serif'],
          mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
        },
      },
    },
  }};
</script>

<!-- LANGKAH 3: Load Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- LANGKAH 4: CSS Kustom Aplikasi (SETELAH Tailwind) -->
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= file_exists(PUBLIC_PATH . '/assets/css/app.css') ? filemtime(PUBLIC_PATH . '/assets/css/app.css') : time() ?>">
