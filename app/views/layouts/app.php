<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\View;

/**
 * Layout terpadu untuk portal staf (Admin & Kasir).
 * Menggantikan duplikasi layouts/admin.php dan layouts/cashier.php.
 * Variabel yang didukung: $title, $content, $activeMenu, $scripts (array).
 */
$authUser = Auth::user();
$role     = Auth::role();
$userName = (string) ($authUser['name'] ?? ($role === 'admin' ? 'Administrator' : 'Kasir'));
$fallbackTitle = $role === 'admin' ? 'Panel Admin' : 'Panel Kasir';
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-white text-ink h-screen overflow-hidden flex font-sans antialiased">
  <?php require APP_PATH . '/views/partials/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
    <header class="h-16 border-b border-line bg-white flex items-center justify-between px-6 shrink-0 z-20">
      <div class="flex items-center gap-2">
        <h1 class="text-lg font-bold tracking-tight text-ink"><?= e($title ?? $fallbackTitle) ?></h1>
      </div>

      <div class="flex items-center gap-2.5 sm:gap-3">
        <?= View::partial('lang-switcher', ['variant' => 'light']) ?>
        <?= View::partial('theme-toggle', ['variant' => 'light']) ?>

        <span class="text-sm font-medium text-ink hidden sm:inline-block"><?= e($userName) ?></span>

        <form id="form-logout" action="<?= e(url('logout.php')) ?>" method="POST" class="inline">
          <?= csrf_field() ?>
          <button
            type="button"
            id="btn-header-logout"
            data-i18n="nav_logout"
            class="h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
          >
            <?= e(__('nav_logout')) ?>
          </button>
        </form>
      </div>
    </header>

    <main class="flex-1 p-6 overflow-y-auto max-w-7xl w-full">
      <?= $content ?? '' ?>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>
  <?= View::partial('scripts-core', ['scripts' => $scripts ?? [], 'includeCart' => false]) ?>
</body>
</html>
