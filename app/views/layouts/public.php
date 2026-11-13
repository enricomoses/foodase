<?php
declare(strict_types=1);

/**
 * Layout halaman publik / pelanggan (Gaya Uber Eats).
 * Variabel yang didukung: $title, $content, $scripts (array).
 */
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-white text-ink min-h-screen flex flex-col font-sans antialiased<?= !empty($immersive) ? ' page-immersive' : '' ?>">
  <?php require APP_PATH . '/views/partials/navbar.php'; ?>

  <!-- Konten Utama (Layar Penuh / Full Screen Responsif) -->
  <main class="<?= !empty($immersive) ? 'pt-0' : 'py-4 sm:py-6' ?> flex-1 mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 pb-28">
    <?= $content ?? '' ?>
  </main>

  <!-- Footer Publik Bersih (Layar Penuh / Full Screen) -->
  <footer class="border-t border-line bg-white py-8 pb-28 mt-auto">
    <div class="mx-auto w-full px-4 sm:px-8 lg:px-12 xl:px-16 2xl:px-20 text-center text-xs text-subtle">
      &copy; <?= date('Y') ?> <?= e(__('footer_copyright')) ?>
    </div>
  </footer>

  <!-- Floating Mobile Bottom Navigation Bar (Gaya Uber Eats - Mobile App UX) -->
  <nav class="fixed bottom-4 left-0 right-0 z-40 px-4 pointer-events-none" aria-label="Navigasi Bawah">
    <div
      id="bottom-nav-container"
      class="bottom-nav-pill mx-auto max-w-[280px] sm:max-w-xs rounded-full px-5 py-2 flex items-center justify-between pointer-events-auto shadow-2xl transition-all"
    >
      <!-- Home / Menu -->
      <a href="<?= e(url()) ?>" class="bottom-nav-link flex flex-col items-center gap-0.5 py-0.5 px-3 rounded-full transition-colors" data-page="menu">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="text-[10px] font-bold tracking-tight" data-i18n="nav_menu"><?= e(__('nav_menu')) ?></span>
      </a>

      <!-- Cari (Focus ke input search) -->
      <button type="button" id="btn-mobile-nav-search" class="bottom-nav-link flex flex-col items-center gap-0.5 py-0.5 px-3 rounded-full transition-colors cursor-pointer" data-page="search">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <span class="text-[10px] font-bold tracking-tight" data-i18n="nav_search"><?= e(__('nav_search')) ?></span>
      </button>

      <!-- Lacak Status Pesanan -->
      <a href="<?= e(url('orders.php')) ?>" class="bottom-nav-link flex flex-col items-center gap-0.5 py-0.5 px-3 rounded-full transition-colors" data-page="orders">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span class="text-[10px] font-bold tracking-tight" data-i18n="nav_track"><?= e(__('nav_track')) ?></span>
      </a>
    </div>
  </nav>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/i18n.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js?v=16')) ?>"></script>
  <script src="<?= e(asset('js/core/cart-store.js?v=16')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
