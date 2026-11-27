<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\View;

$appName = (string) Config::get('app.name', 'FoodEase');
$initial = strtoupper(mb_substr($appName, 0, 1));
?>
<header class="sticky top-0 z-30 border-b border-line bg-white">
  <div class="mx-auto flex h-16 w-full items-center justify-between px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 gap-4">
    <a href="<?= e(url()) ?>" class="flex items-center gap-2.5 hover:opacity-90 transition-opacity shrink-0">
      <span class="tile-n"><?= e($initial) ?></span>
      <span class="wordmark text-lg font-ui font-bold tracking-tight text-ink"><?= e($appName) ?></span>
    </a>

    <div class="hidden md:flex flex-1 max-w-md mx-4">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
          <?= icon('search', 'h-4 w-4') ?>
        </span>
        <input
          type="search"
          id="global-search-input"
          data-i18n-placeholder="nav_search_placeholder"
          placeholder="<?= e(__('nav_search_placeholder')) ?>"
          class="h-10 w-full rounded-full bg-surface pl-10 pr-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        >
      </div>
    </div>

    <nav class="flex items-center gap-2 sm:gap-2.5 shrink-0">
      <a href="<?= e(url('orders.php')) ?>" class="text-sm font-medium text-ink hover:text-subtle transition-colors hidden xs:inline-block" data-i18n="nav_orders">
        <?= e(__('nav_orders')) ?>
      </a>

      <?= View::partial('lang-switcher', ['variant' => 'light']) ?>
      <?= View::partial('theme-toggle', ['variant' => 'light']) ?>

      <a
        href="<?= e(url('cart.php')) ?>"
        class="h-9 px-3.5 sm:px-4 rounded-lg bg-azure text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-azure-600 transition-colors shrink-0 shadow-sm"
        aria-label="Keranjang Belanja"
      >
        <?= icon('cart', 'h-4 w-4 shrink-0') ?>
        <span data-i18n="nav_cart"><?= e(__('nav_cart')) ?></span>
        <span class="opacity-60">&middot;</span>
        <span id="cart-count">0</span>
      </a>
    </nav>
  </div>
</header>
