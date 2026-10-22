<?php
declare(strict_types=1);

use App\Core\Config;

$appName = (string) Config::get('app.name', 'FoodEase');
?>
<!-- Navbar Lengket Gaya Uber Eats -->
<header class="sticky top-0 z-30 border-b border-line bg-white">
  <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 gap-4">
    <!-- Wordmark FoodEase Tebal di Kiri -->
    <a href="<?= e(url()) ?>" class="text-xl font-bold tracking-tight text-ink hover:opacity-80 transition-opacity shrink-0">
      <?= e($appName) ?>
    </a>

    <!-- Kolom Cari Pil di Tengah (Tampil di Desktop, disembunyikan di layar kecil) -->
    <div class="hidden md:flex flex-1 max-w-md mx-4">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle pointer-events-none">
          <?= icon('search', 'h-4 w-4') ?>
        </span>
        <input
          type="search"
          id="global-search-input"
          placeholder="Cari makanan atau minuman..."
          class="h-10 w-full rounded-full bg-surface pl-10 pr-4 text-sm text-ink placeholder-subtle border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
        >
      </div>
    </div>

    <!-- Navigasi Kanan: Tautan Pesanan & Tombol Pil Hitam Keranjang -->
    <nav class="flex items-center gap-3 sm:gap-4 shrink-0">
      <a href="<?= e(url('orders.php')) ?>" class="text-sm font-medium text-ink hover:text-subtle transition-colors">
        Pesanan
      </a>

      <!-- Tombol Pil Hitam Keranjang · n -->
      <a
        href="<?= e(url('cart.php')) ?>"
        class="h-10 px-4 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-1.5 hover:bg-neutral-800 transition-colors"
        aria-label="Keranjang Belanja"
      >
        <span>Keranjang</span>
        <span class="opacity-60">&middot;</span>
        <span id="cart-count">0</span>
      </a>
    </nav>
  </div>
</header>
