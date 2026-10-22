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
        ['key' => 'history',    'label' => 'Riwayat',      'url' => url('cashier/history.php')],
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

  <!-- Footer Sidebar: Versi + Tombol Dark Mode -->
  <div class="border-t border-line p-4 flex items-center justify-between">
    <span class="text-[11px] text-subtle">FoodEase v1.0</span>
    <!-- Tombol Toggle Tema -->
    <button
      type="button"
      id="btn-theme-toggle"
      class="btn-theme-toggle h-8 w-8 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center justify-center text-subtle hover:text-ink"
      title="Beralih ke Mode Gelap"
      aria-label="Ganti tema"
    >
      <!-- Ikon Bulan (Light Mode aktif) -->
      <svg class="icon-moon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
      <!-- Ikon Matahari (Dark Mode aktif) -->
      <svg class="icon-sun h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1" x2="12" y2="3"/>
        <line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1" y1="12" x2="3" y2="12"/>
        <line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
      </svg>
    </button>
  </div>
</aside>
