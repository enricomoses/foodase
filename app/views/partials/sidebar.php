<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;

$currentRole = Auth::role();
$active = $activeMenu ?? '';
$appName = (string) Config::get('app.name', 'FoodEase');

if ($currentRole === 'admin') {
    $menus = [
        ['key' => 'dashboard',  'label' => 'Dashboard',    'url' => url('admin/index.php')],
        ['key' => 'orders',     'label' => 'Pesanan',      'url' => url('admin/orders.php')],
        ['key' => 'menus',      'label' => 'Kelola Menu',  'url' => url('admin/menus.php')],
        ['key' => 'categories', 'label' => 'Kategori',     'url' => url('admin/categories.php')],
        ['key' => 'users',      'label' => 'Pengguna',     'url' => url('admin/users.php')],
        ['key' => 'reports',    'label' => 'Laporan',      'url' => url('admin/reports.php')],
    ];
} else {
    // Role cashier atau default
    $menus = [
        ['key' => 'cashier', 'label' => 'Kasir / Pesanan', 'url' => url('cashier/index.php')],
        ['key' => 'history', 'label' => 'Riwayat',         'url' => url('cashier/history.php')],
    ];
}
?>
<aside class="flex w-56 flex-col border-r border-stone-200 bg-white shrink-0 min-h-screen">
  <!-- Brand logo / header sidebar -->
  <div class="flex h-14 items-center gap-2 border-b border-stone-200 px-4">
    <span class="inline-flex h-6 w-6 items-center justify-center rounded bg-brand text-xs font-bold text-white">FE</span>
    <span class="font-semibold text-stone-900 text-sm tracking-tight"><?= e($appName) ?></span>
    <span class="ml-auto rounded bg-stone-100 px-1.5 py-0.5 text-[10px] uppercase font-medium text-stone-600">
      <?= e((string) $currentRole) ?>
    </span>
  </div>

  <!-- Menu Navigasi -->
  <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
    <?php foreach ($menus as $item): ?>
      <?php $isActive = ($active === $item['key']); ?>
      <a href="<?= e($item['url']) ?>"
         class="flex items-center rounded-md px-3 py-2 text-sm transition-colors <?= $isActive ? 'bg-stone-100 font-medium text-brand border-l-2 border-brand' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' ?>">
        <?= e($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Footer Sidebar info -->
  <div class="border-t border-stone-200 p-3 text-[11px] text-stone-400">
    FoodEase v1.0
  </div>
</aside>
