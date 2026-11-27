<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\I18n;

$appName   = (string) Config::get('app.name', 'FoodEase');
$pageTitle = isset($title) && $title !== ''
    ? e($title) . ' — ' . e($appName)
    : e($appName);
$cssFile = PUBLIC_PATH . '/assets/css/app.css';
$cssVer  = is_file($cssFile) ? (string) filemtime($cssFile) : '1';
$i18nPayload = json_encode([
    'lang'         => current_lang(),
    'translations' => I18n::all(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="app-base-url" content="<?= e(rtrim(url(), '/')) ?>">
<base href="<?= e(rtrim(url(), '/')) ?>/">
<title><?= $pageTitle ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<script id="app-i18n" type="application/json"><?= $i18nPayload ?></script>

<script>
  (function () {
    try {
      var stored = localStorage.getItem('foodease_theme');
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
          accent: { DEFAULT: '#06C167', dark: '#048848', light: '#E6F8EF' },
          danger: { DEFAULT: '#E11900', light: '#FDECEA' },
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

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= e($cssVer) ?>">
