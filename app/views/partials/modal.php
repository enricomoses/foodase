<?php
declare(strict_types=1);
?>
<!-- Modal Konfirmasi Standar untuk UI.confirm() -->
<div id="modal-confirm" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  <!-- Latar belakang gelap -->
  <div class="fixed inset-0 bg-stone-900/50 transition-opacity backdrop-fade" data-modal-dismiss></div>

  <!-- Konten dialog -->
  <div class="flex min-h-full items-center justify-center p-4">
    <div class="relative w-full max-w-md rounded-md bg-white p-6 shadow-md border border-stone-200">
      <h3 id="modal-confirm-title" class="text-base font-semibold text-stone-900">Konfirmasi</h3>
      <p id="modal-confirm-message" class="mt-2 text-sm text-stone-600">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" id="modal-confirm-cancel" class="rounded-md border border-stone-300 bg-white px-3.5 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50" data-modal-dismiss>
          Batal
        </button>
        <button type="button" id="modal-confirm-ok" class="rounded-md bg-brand px-3.5 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">
          Ya, Lanjutkan
        </button>
      </div>
    </div>
  </div>
</div>
