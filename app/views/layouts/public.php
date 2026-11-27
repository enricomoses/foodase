<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\View;

$appName = (string) Config::get('app.name', 'FoodEase');
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="<?= !empty($immersive) ? 'page-immersive' : 'bg-white' ?> text-ink min-h-screen flex flex-col font-sans antialiased">
  <?php if (empty($immersive)): ?>
    <?php require APP_PATH . '/views/partials/navbar.php'; ?>
  <?php endif; ?>

  <main class="<?= !empty($immersive) ? 'p-0 w-full' : 'py-4 sm:py-6 mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20' ?> flex-1">
    <?= $content ?? '' ?>
  </main>

  <?php if (empty($immersive)): ?>
    <footer class="border-t border-line bg-white py-8 mt-auto">
      <div class="mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 text-center text-xs text-subtle">
        &copy; <?= date('Y') ?> <?= e($appName) ?> — <span data-i18n="footer_copyright"><?= e(__('footer_copyright')) ?></span>
      </div>
    </footer>
  <?php endif; ?>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>
  <?= View::partial('scripts-core', ['scripts' => $scripts ?? [], 'includeCart' => true]) ?>
</body>
</html>
