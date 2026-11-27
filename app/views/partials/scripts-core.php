<?php
declare(strict_types=1);

/**
 * Partial daftar script inti bersama + script halaman (versi otomatis berbasis filemtime).
 * Variabel opsional: $includeCart (bool), $scripts (string[]).
 */
$coreScripts = [
    'js/core/api.js',
    'js/core/app.js',
    'js/core/i18n.js',
    'js/core/icons.js',
    'js/core/ui.js',
    'js/core/format.js',
];

if (!empty($includeCart)) {
    $coreScripts[] = 'js/core/cart-store.js';
}

$versioned = static function (string $relPath): string {
    $cleanPath = explode('?', $relPath, 2)[0];
    $fullPath  = PUBLIC_PATH . '/assets/' . ltrim($cleanPath, '/');
    $ver       = is_file($fullPath) ? (string) filemtime($fullPath) : '1';
    return asset($cleanPath) . '?v=' . $ver;
};
?>
<script src="<?= e(asset('vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
<?php foreach ($coreScripts as $core): ?>
  <script src="<?= e($versioned($core)) ?>"></script>
<?php endforeach; ?>

<?php if (!empty($scripts) && is_array($scripts)): ?>
  <?php foreach ($scripts as $script): ?>
    <script src="<?= e($versioned((string) $script)) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
