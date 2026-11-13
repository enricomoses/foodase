<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <?php require APP_PATH . '/views/partials/head.php'; ?>
</head>
<body class="bg-surface text-ink min-h-screen flex items-center justify-center p-4 font-sans antialiased relative">
  <!-- Tombol Bahasa & Toggle Tema di Halaman Auth -->
  <div class="fixed top-4 right-4 z-40 flex items-center gap-2">
    <!-- Pemilih Bahasa (ID / EN / ZH) -->
    <div class="relative" id="lang-dropdown-wrapper">
      <button
        type="button"
        id="btn-lang-toggle"
        class="h-10 px-3 rounded-full bg-white dark:bg-neutral-900 border border-line shadow-sm hover:bg-surface transition-colors flex items-center gap-1.5 text-xs font-bold text-ink cursor-pointer"
        title="Pilih Bahasa / Select Language / 选择语言"
        aria-expanded="false"
      >
        <svg class="h-3.5 w-3.5 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="2" y1="12" x2="22" y2="12"></line>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
        </svg>
        <span id="current-lang-label" class="uppercase font-bold"><?= strtoupper(current_lang()) ?></span>
      </button>
      <div
        id="lang-dropdown-menu"
        class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white border border-line shadow-lg py-1.5 z-50 text-xs"
      >
        <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="id">
          <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">ID</span> <span>Indonesia</span></span>
          <span class="lang-check font-bold text-accent <?= current_lang() === 'id' ? '' : 'hidden' ?>">✓</span>
        </button>
        <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="en">
          <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">EN</span> <span>English</span></span>
          <span class="lang-check font-bold text-accent <?= current_lang() === 'en' ? '' : 'hidden' ?>">✓</span>
        </button>
        <button type="button" class="lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer" data-lang="zh">
          <span class="flex items-center gap-2.5"><span class="w-5 text-center font-bold text-[11px] text-subtle">ZH</span> <span>中文 (Mandarin)</span></span>
          <span class="lang-check font-bold text-accent <?= current_lang() === 'zh' ? '' : 'hidden' ?>">✓</span>
        </button>
      </div>
    </div>

    <button
      type="button"
      class="btn-theme-toggle h-10 w-10 rounded-full bg-white dark:bg-neutral-900 border border-line shadow-sm hover:bg-surface transition-colors flex items-center justify-center text-subtle hover:text-ink cursor-pointer"
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

  <!-- Kartu Tengah Bersih (rounded-2xl border-line bg-white) -->
  <div class="w-full max-w-md rounded-2xl border border-line bg-white p-6 sm:p-8 shadow-sm">
    <?= $content ?? '' ?>
  </div>

  <?php require APP_PATH . '/views/partials/modal.php'; ?>
  <?php require APP_PATH . '/views/partials/toast.php'; ?>

  <!-- Script Bersama -->
  <script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= e(asset('js/core/app.js?v=12')) ?>"></script>
  <script src="<?= e(asset('js/core/i18n.js?v=12')) ?>"></script>
  <script src="<?= e(asset('js/core/api.js?v=12')) ?>"></script>
  <script src="<?= e(asset('js/core/icons.js?v=12')) ?>"></script>
  <script src="<?= e(asset('js/core/ui.js?v=12')) ?>"></script>
  <script src="<?= e(asset('js/core/format.js?v=12')) ?>"></script>

  <!-- Script Halaman -->
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
      <script src="<?= e(asset($script)) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
