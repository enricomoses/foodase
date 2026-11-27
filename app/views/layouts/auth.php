<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\View;

$appName  = (string) Config::get('app.name', 'FoodEase');
$initial  = strtoupper(mb_substr($appName, 0, 1));
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="fe-auth min-h-screen font-sans antialiased bg-n-900 text-n-25">
  <div class="fe-auth-shell">
    <aside class="fe-auth-aside bg-n-900 border-r border-n-700/50 flex flex-col justify-between">
      <div class="p-8 lg:p-12">
        <a href="<?= e(url()) ?>" class="inline-flex items-center gap-3 group">
          <span class="tile-n"><?= e($initial) ?></span>
          <span class="wordmark text-xl text-white group-hover:text-azure-300 transition-colors">
            <?= e($appName) ?>
          </span>
        </a>
      </div>

      <div class="p-8 lg:p-12 space-y-4">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-azure/10 border border-azure/30 text-[11px] font-semibold text-azure-300 tracking-wide uppercase">
          <span class="w-1.5 h-1.5 rounded-full bg-azure animate-pulse"></span>
          <span>Coffee, Kitchen &amp; Milk Bar</span>
        </div>
        <h2 class="font-ui font-bold text-3xl lg:text-4xl leading-tight text-white tracking-tight">
          Diracik Segar.<br><span class="text-azure-300">Nikmati Momen.</span><br>Tanpa Antre.
        </h2>
        <p class="max-w-sm text-sm leading-relaxed text-n-400">
          Kopi pilihan nusantara, minuman signature creamy, dan aneka menu lezat. Pesan dari meja, pantau status secara live.
        </p>
      </div>

      <div class="p-8 lg:p-12 text-[11px] font-mono tracking-wider text-n-500 uppercase">
        &copy; <?= date('Y') ?> &mdash; <?= e($appName) ?>
      </div>
    </aside>

    <main class="fe-auth-main bg-n-900/95 sm:bg-transparent">
      <div class="fixed top-4 right-4 z-40 flex items-center gap-2">
        <?= View::partial('lang-switcher', ['variant' => 'dark']) ?>
        <?= View::partial('theme-toggle', ['variant' => 'dark']) ?>
      </div>

      <div class="fe-auth-card">
        <?= $content ?? '' ?>
      </div>

      <p class="mt-8 text-center text-[10px] font-mono uppercase tracking-[0.2em] text-n-500">
        &copy; <?= date('Y') ?> <?= e($appName) ?> &bull; All Rights Reserved
      </p>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>
  <?= View::partial('scripts-core', ['scripts' => $scripts ?? [], 'includeCart' => false]) ?>
</body>
</html>
