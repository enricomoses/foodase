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

    <!-- Navigasi Kanan: Tautan Pesanan, Toggle Tema, Akun, Keranjang -->
    <nav class="flex items-center gap-2 sm:gap-3 shrink-0">
      <a href="<?= e(url('orders.php')) ?>" class="text-sm font-medium text-ink hover:text-subtle transition-colors hidden xs:inline-block">
        Pesanan
      </a>

      <!-- Tombol Toggle Tema (Dark/Light) -->
      <button
        type="button"
        class="btn-theme-toggle h-9 w-9 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center justify-center text-subtle hover:text-ink cursor-pointer"
        title="Beralih Mode Gelap/Terang"
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

      <!-- Logo / Tombol Akun di Kanan Atas -->
      <?php if (\App\Core\Auth::check()): ?>
        <?php $currentUser = \App\Core\Auth::user(); ?>
        <div class="relative" id="account-dropdown-wrapper">
          <button
            type="button"
            id="btn-account-menu"
            class="h-9 px-2.5 sm:px-3 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center gap-1.5 text-ink text-xs font-semibold cursor-pointer border border-line"
            title="Menu Akun: <?= e($currentUser['name'] ?? 'Pengguna') ?>"
            aria-expanded="false"
          >
            <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px] font-bold shrink-0">
              <?= e(strtoupper(substr((string) ($currentUser['name'] ?? 'U'), 0, 1))) ?>
            </span>
            <span class="hidden md:inline max-w-[90px] truncate"><?= e($currentUser['name'] ?? 'Akun') ?></span>
            <svg class="h-3 w-3 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <!-- Popover Dropdown Akun -->
          <div
            id="account-dropdown-menu"
            class="hidden absolute right-0 mt-2 w-52 rounded-xl bg-white border border-line shadow-lg py-1.5 z-50 text-xs"
          >
            <div class="px-3.5 py-2 border-b border-line">
              <p class="font-bold text-ink truncate"><?= e($currentUser['name'] ?? 'Pengguna') ?></p>
              <p class="text-subtle text-[11px] font-medium uppercase tracking-wider mt-0.5">
                <?= ($currentUser['role'] ?? '') === 'admin' ? 'Administrator Kafe' : 'Kasir Kafe' ?>
              </p>
            </div>
            <a
              href="<?= e(($currentUser['role'] ?? '') === 'admin' ? url('admin/') : url('cashier/')) ?>"
              class="flex items-center gap-2 px-3.5 py-2 text-ink hover:bg-surface transition-colors"
            >
              <svg class="h-4 w-4 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              <span>Dashboard <?= ($currentUser['role'] ?? '') === 'admin' ? 'Admin' : 'Kasir' ?></span>
            </a>
            <a
              href="<?= e(url('logout.php')) ?>"
              class="flex items-center gap-2 px-3.5 py-2 text-danger hover:bg-danger-light transition-colors"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>Keluar</span>
            </a>
          </div>
        </div>
      <?php else: ?>
        <a
          href="<?= e(url('login.php')) ?>"
          class="h-9 px-2.5 sm:px-3 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center gap-1.5 text-subtle hover:text-ink cursor-pointer border border-line"
          title="Masuk Akun Staf / Admin"
          aria-label="Akun Staf"
        >
          <svg class="h-4 w-4 shrink-0 text-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="text-xs font-semibold text-ink hidden sm:inline">Akun</span>
        </a>
      <?php endif; ?>

      <!-- Tombol Pil Hitam Keranjang · n -->
      <a
        href="<?= e(url('cart.php')) ?>"
        class="h-10 px-3.5 sm:px-4 rounded-full bg-black text-white text-sm font-medium inline-flex items-center gap-1.5 hover:bg-neutral-800 transition-colors shrink-0"
        aria-label="Keranjang Belanja"
      >
        <span>Keranjang</span>
        <span class="opacity-60">&middot;</span>
        <span id="cart-count">0</span>
      </a>
    </nav>

  </div>
</header>
