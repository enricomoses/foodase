<?php
declare(strict_types=1);

use App\Core\Config;

$cafeName = (string) Config::get('app.name', 'FoodEase');
?>
<header class="border-b border-stone-200 bg-white sticky top-0 z-30">
  <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6">
    <!-- Brand / Nama Kafe -->
    <a href="<?= e(url()) ?>" class="flex items-center gap-2 font-semibold text-lg text-stone-900 hover:text-brand">
      <span class="inline-block h-6 w-6 rounded bg-brand text-white text-center text-sm leading-6 font-bold">F</span>
      <span><?= e($cafeName) ?></span>
    </a>

    <!-- Navigasi Pelanggan -->
    <nav class="flex items-center gap-4">
      <a href="<?= e(url('orders.php')) ?>" class="text-sm font-medium text-stone-600 hover:text-stone-900">
        Pesanan Saya
      </a>

      <!-- Tombol Keranjang -->
      <a href="<?= e(url('cart.php')) ?>" class="relative inline-flex items-center justify-center rounded-md border border-stone-200 p-2 text-stone-700 hover:bg-stone-50" aria-label="Keranjang Pesanan">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>
        <!-- Badge Jumlah Item Keranjang -->
        <span id="cart-count" class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white">
          0
        </span>
      </a>
    </nav>
  </div>
</header>
