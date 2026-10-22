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
        ['key' => 'pos',        'label' => 'POS',          'url' => url('cashier/pos.php')],
        ['key' => 'menus',      'label' => 'Kelola Menu',  'url' => url('admin/menus.php')],
        ['key' => 'categories', 'label' => 'Kategori',     'url' => url('admin/categories.php')],
        ['key' => 'users',      'label' => 'Pengguna',     'url' => url('admin/users.php')],
        ['key' => 'reports',    'label' => 'Laporan',      'url' => url('admin/reports.php')],
    ];
} else {
    // Role cashier atau default
    $menus = [
        ['key' => 'cashier', 'label' => 'Pesanan Masuk',   'url' => url('cashier/index.php')],
        ['key' => 'pos',     'label' => 'POS',             'url' => url('cashier/pos.php')],
        ['key' => 'history', 'label' => 'Riwayat',         'url' => url('cashier/history.php')],
    ];
}
?>
<!-- Sidebar Putih Berborder Kanan (Gaya Uber Eats) -->
<aside class="flex w-64 flex-col border-r border-line bg-white shrink-0 min-h-screen">
  <!-- Brand Header -->
  <div class="flex h-16 items-center justify-between border-b border-line px-5">
    <a href="<?= e(url()) ?>" class="font-bold text-ink text-lg tracking-tight hover:opacity-80 transition-opacity">
      <?= e($appName) ?>
    </a>
    <span class="rounded-full bg-surface px-2.5 py-0.5 text-[11px] font-medium text-subtle uppercase">
      <?= e((string) $currentRole) ?>
    </span>
  </div>

  <!-- Menu Navigasi -->
  <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
    <?php foreach ($menus as $item): ?>
      <?php $isActive = ($active === $item['key']); ?>
      <a
        href="<?= e($item['url']) ?>"
        class="flex h-11 items-center px-3.5 text-sm transition-colors rounded-lg <?= $isActive ? 'bg-surface font-bold text-ink' : 'font-medium text-subtle hover:bg-surface hover:text-ink' ?>"
      >
        <?= e($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Footer Sidebar -->
  <div class="border-t border-line p-4 text-[11px] text-subtle">
    FoodEase v1.0
  </div>
</aside>
