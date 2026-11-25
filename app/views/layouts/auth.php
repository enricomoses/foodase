<?php
declare(strict_types=1);

use App\Core\Config;

$appName = (string) Config::get('app.name', 'Nola');
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="fe-auth min-h-screen font-sans antialiased bg-n-900 text-n-25">
  <div class="fe-auth-shell">
    <!-- Panel kiri: brand editorial (desktop) -->
    <aside class="fe-auth-aside bg-n-900 border-r border-n-700/50 flex flex-col justify-between">
      <div class="p-8 lg:p-12">
        <a href="<?= e(url()) ?>" class="inline-flex items-center gap-3 group">
          <span class="tile-n">N</span>
          <span class="wordmark text-xl text-white group-hover:text-azure-300 transition-colors">
            <?= e($appName) ?>
          </span>
        </a>
      </div>

      <div class="p-8 lg:p-12 space-y-4">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-azure/10 border border-azure/30 text-[11px] font-semibold text-azure-300 tracking-wide uppercase">
          <span class="w-1.5 h-1.5 rounded-full bg-azure animate-pulse"></span>
          <span>Coffee, Kitchen &amp; Milk Bar</span>
        </div>
        <h2 class="font-ui font-bold text-3xl lg:text-4xl leading-tight text-white tracking-tight">
          Diracik Segar.<br><span class="text-azure-300">Nikmati Momen.</span><br>Tanpa Antre.
        </h2>
        <p class="max-w-sm text-sm leading-relaxed text-n-400">
          Kopi pilihan nusantara, minuman signature creamy, dan aneka menu lezat. Pesan dari meja, pantau status secara live.
        </p>
      </div>

      <div class="p-8 lg:p-12 text-[11px] font-mono tracking-wider text-n-500 uppercase">
        Est. 2024 &mdash; Nola Experience
      </div>
    </aside>

    <main class="fe-auth-main bg-n-900/95 sm:bg-transparent">
      <!-- Tombol Bahasa & Toggle Tema di Halaman Auth -->
      <div class="fixed top-4 right-4 z-40 flex items-center gap-2">
        <!-- Pemilih Bahasa (ID / EN / ZH) -->
        <div class="relative" id="lang-dropdown-wrapper">
          <button
            type="button"
            id="btn-lang-toggle"
            class="h-9 px-3 rounded-lg bg-n-800/80 border border-n-700/60 hover:border-n-500 text-n-25 transition-all flex items-center gap-1.5 text-xs font-semibold cursor-pointer shadow-sm"
            title="Pilih Bahasa / Select Language / 选择语言"
            aria-expanded="false"
          >
            <svg class="h-3.5 w-3.5 text-n-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <span id="current-lang-label" class="uppercase font-mono font-bold"><?= strtoupper(current_lang()) ?></span>
          </button>
          <div
            id="lang-dropdown-menu"
            class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-n-800 border border-n-700 shadow-xl py-1.5 z-50 text-xs"
          >
            <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-n-25 hover:bg-n-700 transition-colors cursor-pointer" data-lang="id">
              <span class="flex items-center gap-2.5"><span class="w-5 text-center font-mono font-bold text-[11px] text-n-400">ID</span> <span>Indonesia</span></span>
              <span class="lang-check font-bold text-azure <?= current_lang() === 'id' ? '' : 'hidden' ?>">✓</span>
            </button>
            <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-n-25 hover:bg-n-700 transition-colors cursor-pointer" data-lang="en">
              <span class="flex items-center gap-2.5"><span class="w-5 text-center font-mono font-bold text-[11px] text-n-400">EN</span> <span>English</span></span>
              <span class="lang-check font-bold text-azure <?= current_lang() === 'en' ? '' : 'hidden' ?>">✓</span>
            </button>
            <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-n-25 hover:bg-n-700 transition-colors cursor-pointer" data-lang="zh">
              <span class="flex items-center gap-2.5"><span class="w-5 text-center font-mono font-bold text-[11px] text-n-400">ZH</span> <span>中文 (Mandarin)</span></span>
              <span class="lang-check font-bold text-azure <?= current_lang() === 'zh' ? '' : 'hidden' ?>">✓</span>
            </button>
          </div>
        </div>

        <button
          type="button"
          class="btn-theme-toggle h-9 w-9 rounded-lg bg-n-800/80 border border-n-700/60 hover:border-n-500 text-n-400 hover:text-white transition-all flex items-center justify-center cursor-pointer shadow-sm"
          title="Beralih Mode Gelap/Terang"
          aria-label="Ganti tema"
        >
          <svg class="icon-moon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
          </svg>
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

      <!-- Kartu Form -->
      <div class="fe-auth-card">
        <?= $content ?? '' ?>
      </div>

      <p class="mt-8 text-center text-[10px] font-mono uppercase tracking-[0.2em] text-n-500">
        &copy; <?= date('Y') ?> <?= e($appName) ?> &bull; All Rights Reserved
      </p>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js?v=17')) ?>"></script>
  <script src="<?= e(asset('js/core/i18n.js?v=17')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js?v=17')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js?v=17')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js?v=17')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js?v=17')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
