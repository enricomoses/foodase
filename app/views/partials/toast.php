<?php
declare(strict_types=1);
?>
<!-- Container Notifikasi Toast Modern Gaya Image 2 di Kanan Atas -->
<div id="toast-container" class="fixed top-5 right-5 sm:top-6 sm:right-6 z-[9999] flex flex-col gap-3 w-[92vw] sm:w-[400px] max-w-md pointer-events-none" aria-live="polite">
  <?php
    $flashToast = \App\Core\Session::pullFlash('flash_toast');
    if (!empty($flashToast) && is_array($flashToast)):
  ?>
    <div id="php-flash-toast"
         class="hidden"
         data-type="<?= e((string)($flashToast['type'] ?? 'info')) ?>"
         data-title="<?= e((string)($flashToast['title'] ?? '')) ?>"
         data-message="<?= e((string)($flashToast['message'] ?? '')) ?>">
    </div>
  <?php endif; ?>
</div>

