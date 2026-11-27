<?php
declare(strict_types=1);

/**
 * Partial input password + Caps Lock warning + strength meter + 4 checklist keamanan.
 * Variabel: $prefix ('create' | 'reset'), $inputId (string).
 */
$prefix  = (string) ($prefix ?? 'create');
$inputId = (string) ($inputId ?? $prefix . '-password');
?>
<div class="pt-1">
  <div class="floating-group">
    <input
      type="password"
      id="<?= e($inputId) ?>"
      name="password"
      required
      placeholder=" "
      class="floating-input h-14 w-full rounded-lg bg-surface px-4 pr-12 text-sm text-ink border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
    >
    <label for="<?= e($inputId) ?>" class="floating-label">
      Password Baru <span class="text-danger">*</span>
    </label>
    <button
      type="button"
      class="btn-toggle-pw absolute right-3 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center text-subtle hover:text-ink focus:outline-none"
      data-target="#<?= e($inputId) ?>"
      title="Tampilkan / Sembunyikan Password"
    >
      <span class="icon-eye"><?= icon('eye', 'h-4 w-4') ?></span>
      <span class="icon-eye-off hidden"><?= icon('eye-off', 'h-4 w-4') ?></span>
    </button>
  </div>

  <div id="<?= e($prefix) ?>-caps-warning" class="hidden mt-1.5 flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1 border border-amber-200">
    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <span>Caps Lock is ON</span>
  </div>

  <div class="mt-3 space-y-1.5">
    <div class="flex items-center justify-between text-xs">
      <span class="font-medium text-subtle">Kekuatan Password:</span>
      <span id="<?= e($prefix) ?>-strength-label" class="font-semibold text-subtle">-</span>
    </div>
    <div class="grid grid-cols-4 gap-1.5 h-1.5">
      <div id="<?= e($prefix) ?>-bar-1" class="h-full rounded-full bg-line transition-colors"></div>
      <div id="<?= e($prefix) ?>-bar-2" class="h-full rounded-full bg-line transition-colors"></div>
      <div id="<?= e($prefix) ?>-bar-3" class="h-full rounded-full bg-line transition-colors"></div>
      <div id="<?= e($prefix) ?>-bar-4" class="h-full rounded-full bg-line transition-colors"></div>
    </div>
  </div>

  <div class="mt-3.5 rounded-lg bg-surface p-3 border border-line space-y-2 text-xs">
    <p class="font-semibold text-ink text-[11px] uppercase tracking-wider">Persyaratan Keamanan:</p>
    <ul class="space-y-1.5">
      <?php
      $reqs = [
          'length'    => 'Minimal 8 karakter',
          'uppercase' => 'Minimal 1 huruf besar (A-Z)',
          'number'    => 'Minimal 1 angka (0-9)',
          'special'   => 'Minimal 1 karakter khusus (!@#$%^&*...)',
      ];
      foreach ($reqs as $key => $label):
      ?>
      <li id="<?= e($prefix) ?>-req-<?= e($key) ?>" class="flex items-center gap-2 text-subtle transition-colors">
        <span class="req-icon flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-line text-subtle">
          <span class="dot h-1.5 w-1.5 rounded-full bg-subtle"></span>
        </span>
        <span><?= e($label) ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <p id="error-<?= e($prefix) ?>-password" class="hidden mt-1.5 text-xs font-medium text-danger"></p>
</div>
