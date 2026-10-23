<?php
declare(strict_types=1);

/**
 * Layout halaman publik / pelanggan (Gaya Uber Eats).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-white text-ink min-h-screen flex flex-col font-sans antialiased">
  <?php require APP_PATH . '/views/partials/navbar.php'; ?>

  <!-- Konten Utama (Maksimal 6xl sesuai prd.md §12.3, padding bottom untuk mobile bar) -->
  <main class="flex-1 mx-auto max-w-6xl w-full p-4 sm:p-6 pb-24 md:pb-6">
    <?= $content ?? '' ?>
  </main>

  <!-- Footer Publik Bersih (padding ekstra di mobile) -->
  <footer class="border-t border-line bg-white py-8 pb-24 md:pb-8 mt-auto">
    <div class="mx-auto max-w-6xl px-4 text-center text-xs text-subtle">
      &copy; <?= date('Y') ?> FoodEase — Sistem Pemesanan & Kasir Kafe
    </div>
  </footer>

  <!-- Floating Mobile Bottom Navigation Bar (Gaya Image 1 - Mobile App UX) -->
  <nav class="block md:hidden fixed bottom-3 left-3 right-3 z-40" aria-label="Navigasi Bawah">
    <div class="mx-auto max-w-md bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-line rounded-full shadow-2xl px-3.5 py-1.5 flex items-center justify-between">
      <!-- Home / Menu -->
      <a href="<?= e(url('index.php')) ?>" class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-full text-ink hover:text-ink transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="text-[10px] font-semibold">Menu</span>
      </a>

      <!-- Cari (Focus ke input search) -->
      <button type="button" id="btn-mobile-nav-search" class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-full text-subtle hover:text-ink transition-colors cursor-pointer">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <span class="text-[10px] font-semibold">Cari</span>
      </button>

      <!-- Cart Active Pill (Persis Desain Image 1) -->
      <a href="<?= e(url('cart.php')) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-black text-white text-xs font-bold shadow-md hover:bg-neutral-800 transition-all cursor-pointer">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span>Cart</span>
        <span class="cart-badge-count flex h-4 min-w-[16px] items-center justify-center rounded-full bg-accent text-[10px] font-extrabold text-white px-1">0</span>
      </a>

      <!-- Lacak Status Pesanan -->
      <a href="<?= e(url('orders.php')) ?>" class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-full text-subtle hover:text-ink transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        <span class="text-[10px] font-semibold">Lacak</span>
      </a>

      <!-- Akun Masuk Staf / Admin -->
      <a href="<?= e(url('login.php')) ?>" class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-full text-subtle hover:text-ink transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <span class="text-[10px] font-semibold">Akun</span>
      </a>
    </div>
  </nav>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js')) ?>"></script>
  <script src="<?= e(asset('js/core/cart-store.js')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
