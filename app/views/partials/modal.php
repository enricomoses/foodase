<?php
declare(strict_types=1);
?>
<!-- Modal Konfirmasi Standar untuk UI.confirm() (Bottom Sheet di Mobile) -->
<div id="modal-confirm" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <!-- Latar belakang gelap -->
  <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <!-- Konten dialog (Bottom sheet di mobile, modal tengah di sm+) -->
  <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[90vh] overflow-y-auto">
      <!-- Handle drag indicator di mobile -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>

      <h3 id="modal-confirm-title" class="text-xl font-bold tracking-tight text-ink">Konfirmasi</h3>
      <p id="modal-confirm-message" class="mt-2 text-sm text-subtle leading-relaxed">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>

      <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
        <button type="button" id="modal-confirm-cancel" class="h-12 px-6 rounded-full bg-surface text-ink font-medium hover:bg-neutral-200 transition-colors" data-modal-dismiss>
          Batal
        </button>
        <button type="button" id="modal-confirm-ok" class="h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800 transition-colors">
          Ya, Lanjutkan
        </button>
      </div>
    </div>
  </div>
</div>
