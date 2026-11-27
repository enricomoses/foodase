<?php
declare(strict_types=1);

/**
 * Partial dropdown pemilih bahasa (ID / EN / ZH).
 * Variabel opsional: $variant ('light' | 'dark').
 */
$isDark  = ($variant ?? 'light') === 'dark';
$curLang = current_lang();

$btnClass = $isDark
    ? 'h-9 px-3 rounded-lg bg-n-800/80 border border-n-700/60 hover:border-n-500 text-n-25 transition-all flex items-center gap-1.5 text-xs font-semibold cursor-pointer shadow-sm'
    : 'h-9 px-2.5 rounded-full bg-surface hover:bg-neutral-200 transition-colors flex items-center gap-1.5 text-xs font-bold text-ink border border-line cursor-pointer';
$menuClass = $isDark
    ? 'hidden absolute right-0 mt-2 w-44 rounded-xl bg-n-800 border border-n-700 shadow-xl py-1.5 z-50 text-xs'
    : 'hidden absolute right-0 mt-2 w-44 rounded-xl bg-white border border-line shadow-lg py-1.5 z-50 text-xs';
$itemClass = $isDark
    ? 'lang-option w-full flex items-center justify-between px-3.5 py-2 text-n-25 hover:bg-n-700 transition-colors cursor-pointer'
    : 'lang-option w-full flex items-center justify-between px-3.5 py-2 text-ink hover:bg-surface transition-colors cursor-pointer';
$codeClass = $isDark
    ? 'w-5 text-center font-mono font-bold text-[11px] text-n-400'
    : 'w-5 text-center font-bold text-[11px] text-subtle';
$checkClass = $isDark ? 'lang-check font-bold text-azure' : 'lang-check font-bold text-accent';

$options = [
    ['code' => 'id', 'short' => 'ID', 'label' => 'Indonesia'],
    ['code' => 'en', 'short' => 'EN', 'label' => 'English'],
    ['code' => 'zh', 'short' => 'ZH', 'label' => '中文 (Mandarin)'],
];
?>
<div class="relative" id="lang-dropdown-wrapper">
  <button
    type="button"
    id="btn-lang-toggle"
    class="<?= e($btnClass) ?>"
    title="Pilih Bahasa / Select Language / 选择语言"
    aria-expanded="false"
  >
    <svg class="h-3.5 w-3.5 <?= $isDark ? 'text-n-400' : 'text-subtle' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="2" y1="12" x2="22" y2="12"></line>
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
    </svg>
    <span id="current-lang-label" class="uppercase font-bold"><?= e(strtoupper($curLang)) ?></span>
    <?php if (!$isDark): ?>
      <svg class="h-3 w-3 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    <?php endif; ?>
  </button>
  <div id="lang-dropdown-menu" class="<?= e($menuClass) ?>">
    <?php foreach ($options as $opt): ?>
      <button type="button" class="<?= e($itemClass) ?>" data-lang="<?= e($opt['code']) ?>">
        <span class="flex items-center gap-2.5">
          <span class="<?= e($codeClass) ?>"><?= e($opt['short']) ?></span>
          <span><?= e($opt['label']) ?></span>
        </span>
        <span class="<?= e($checkClass) ?> <?= $curLang === $opt['code'] ? '' : 'hidden' ?>">✓</span>
      </button>
    <?php endforeach; ?>
  </div>
</div>
