<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;

$currentRole = Auth::role();
$active      = $activeMenu ?? '';
$appName     = (string) Config::get('app.name', 'FoodEase');
$initial     = strtoupper(mb_substr($appName, 0, 1));

if ($currentRole === 'admin') {
    $menus = [
        ['key' => 'dashboard',  'label' => __('sidebar_dashboard'),  'url' => url('admin/index.php')],
        ['key' => 'orders',     'label' => __('sidebar_orders'),     'url' => url('admin/orders.php')],
        ['key' => 'pos',        'label' => __('sidebar_pos'),        'url' => url('cashier/pos.php')],
        ['key' => 'history',    'label' => __('sidebar_history'),    'url' => url('cashier/history.php')],
        ['key' => 'menus',      'label' => __('sidebar_menus'),      'url' => url('admin/menus.php')],
        ['key' => 'categories', 'label' => __('sidebar_categories'), 'url' => url('admin/categories.php')],
        ['key' => 'users',      'label' => __('sidebar_users'),      'url' => url('admin/users.php')],
        ['key' => 'reports',    'label' => __('sidebar_reports'),    'url' => url('admin/reports.php')],
    ];
} else {
    $menus = [
        ['key' => 'cashier', 'label' => __('sidebar_cashier'), 'url' => url('cashier/index.php')],
        ['key' => 'pos',     'label' => __('sidebar_pos'),     'url' => url('cashier/pos.php')],
        ['key' => 'history', 'label' => __('sidebar_history'), 'url' => url('cashier/history.php')],
    ];
}
?>
<aside class="flex w-64 flex-col border-r border-line bg-white shrink-0 h-screen sticky top-0">
  <div class="flex h-16 items-center justify-between border-b border-line px-5">
    <a href="<?= e(url()) ?>" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
      <span class="tile-n"><?= e($initial) ?></span>
      <span class="font-ui font-bold text-ink text-base tracking-tight"><?= e($appName) ?></span>
    </a>
    <span class="rounded-full bg-surface px-2.5 py-0.5 text-[11px] font-medium text-subtle uppercase">
      <?= e((string) $currentRole) ?>
    </span>
  </div>

  <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
    <?php foreach ($menus as $item): ?>
      <?php $isActive = ($active === $item['key']); ?>
      <a
        href="<?= e($item['url']) ?>"
        class="flex h-10 items-center px-3.5 text-xs transition-colors rounded-lg <?= $isActive ? 'bg-azure text-white font-semibold shadow-sm' : 'font-medium text-subtle hover:bg-surface hover:text-ink' ?>"
        data-i18n="sidebar_<?= e($item['key']) ?>"
      >
        <?= e($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="border-t border-line p-4 flex items-center justify-between">
    <span class="text-[11px] text-subtle"><?= e($appName) ?> v1.0</span>
  </div>
</aside>
