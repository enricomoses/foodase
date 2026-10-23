/**
 * Komponen & Utility Antarmuka Pengguna (UI) — Gaya Uber Eats sesuai prd.md §12.
 */
window.UI = (function ($) {
  'use strict';

  // Inisialisasi event listener global untuk menutup modal dengan Escape dan klik latar
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      $('.fixed[role="dialog"]:not(.hidden)').each(function () {
        UI.closeModal(this.id);
      });
    }
  });

  $(document).on('click', '[data-modal-dismiss]', function () {
    const modal = $(this).closest('.fixed[role="dialog"]');
    if (modal.length) {
      UI.closeModal(modal.attr('id'));
    }
  });

  return {
    /**
     * Tampilkan notifikasi toast modern sesuai Image 2 (Dark capsule, circular badge, progress bar).
     * @param {'success'|'error'|'info'|'warning'} type
     * @param {string} message Deskripsi pesan
     * @param {string} [title] Judul notifikasi (opsional, default otomatis sesuai type)
     * @param {number} [duration=4000] Durasi tayang dalam milidetik
     */
    toast: function (type, message, title, duration) {
      let container = $('#toast-container');
      if (!container.length) {
        container = $(`
          <div id="toast-container" class="fixed top-5 right-5 sm:top-6 sm:right-6 z-[9999] flex flex-col gap-3 w-[92vw] sm:w-[400px] max-w-md pointer-events-none" aria-live="polite"></div>
        `);
        $('body').append(container);
      }

      // Judul default jika tidak disediakan
      const defaultTitles = {
        success: 'Berhasil!',
        error:   'Terjadi Kesalahan',
        warning: 'Perhatian',
        info:    'Informasi'
      };

      const resolvedTitle = title || defaultTitles[type] || 'Notifikasi';
      const resolvedDuration = (typeof duration === 'number' && duration > 0) ? duration : 4000;

      // Konfigurasi ikon & warna sesuai Image 2 (lingkaran besar di kiri)
      let badgeBg = 'bg-[#22c55e]'; // Hijau emerald
      let progressColor = 'bg-[#22c55e]';
      let iconSvg = `
        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      `;

      if (type === 'error') {
        badgeBg = 'bg-[#ef4444]'; // Merah
        progressColor = 'bg-[#ef4444]';
        iconSvg = `
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        `;
      } else if (type === 'warning') {
        badgeBg = 'bg-[#f59e0b]'; // Kuning amber
        progressColor = 'bg-[#f59e0b]';
        iconSvg = `
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        `;
      } else if (type === 'info') {
        badgeBg = 'bg-[#3b82f6]'; // Biru info
        progressColor = 'bg-[#3b82f6]';
        iconSvg = `
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        `;
      }

      // Template notifikasi persis seperti Image 2
      const $toast = $(`
        <div class="toast-item relative flex flex-col p-4 sm:p-4.5 cursor-pointer select-none">
          <div class="flex items-start gap-3.5">
            <!-- Lingkaran besar dengan ikon di kiri (persis Image 2) -->
            <div class="w-11 h-11 rounded-full ${badgeBg} flex items-center justify-center shrink-0 shadow-md">
              ${iconSvg}
            </div>

            <!-- Konten Teks di Tengah -->
            <div class="flex-1 min-w-0 pr-1">
              <h4 class="text-white font-bold text-sm sm:text-base leading-tight tracking-tight">
                ${UI.escape(resolvedTitle)}
              </h4>
              <p class="text-neutral-300 text-xs sm:text-[13px] mt-1 leading-snug">
                ${UI.escape(message)}
              </p>
            </div>

            <!-- Tombol Tutup (X) di Kanan Atas -->
            <button type="button" class="toast-close-btn -mr-1 -mt-1 p-1 text-neutral-400 hover:text-white rounded-lg hover:bg-white/10 transition-colors shrink-0 cursor-pointer" aria-label="Tutup">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Progress bar horizontal di bagian bawah -->
          <div class="toast-progress-track">
            <div class="toast-progress-fill ${progressColor}"></div>
          </div>
        </div>
      `);

      const $fill = $toast.find('.toast-progress-fill');
      let startTime = Date.now();
      let remaining = resolvedDuration;
      let timerId = null;
      let animFrameId = null;
      let isPaused = false;

      function updateProgress() {
        if (!isPaused) {
          const elapsed = Date.now() - startTime;
          const percent = Math.max(0, 100 - (elapsed / resolvedDuration) * 100);
          $fill.css('width', percent + '%');

          if (percent <= 0) {
            dismissToast();
            return;
          }
        }
        animFrameId = requestAnimationFrame(updateProgress);
      }

      function dismissToast() {
        if (animFrameId) cancelAnimationFrame(animFrameId);
        if (timerId) clearTimeout(timerId);

        $toast.addClass('toast-leave');
        setTimeout(function () {
          $toast.remove();
        }, 220);
      }

      // Pause saat kursor berada di atas toast
      $toast.on('mouseenter', function () {
        isPaused = true;
        remaining -= Date.now() - startTime;
      });

      $toast.on('mouseleave', function () {
        isPaused = false;
        startTime = Date.now() - (resolvedDuration - remaining);
      });

      // Klik close atau klik toast untuk langsung menutup
      $toast.find('.toast-close-btn').on('click', function (e) {
        e.stopPropagation();
        dismissToast();
      });

      $toast.on('click', function () {
        dismissToast();
      });

      // Tambahkan ke container
      container.append($toast);
      animFrameId = requestAnimationFrame(updateProgress);
    },

    /**
     * Dialog konfirmasi berbasis Promise kustom (TANPA confirm bawaan browser Chrome).
     * @param {string} message
     * @param {string} [title='Konfirmasi']
     * @returns {Promise<boolean>}
     */
    confirm: function (message, title) {
      return new Promise(function (resolve) {
        let modal = $('#modal-confirm');
        if (!modal.length) {
          // Buat modal dinamis jika belum ada di DOM
          const dynamicModal = $(`
            <div id="modal-confirm" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
              <div class="fixed inset-0 bg-black/50 transition-opacity backdrop-fade" data-modal-dismiss></div>
              <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div class="relative w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white p-6 shadow-lg border-t sm:border border-line max-h-[90vh] overflow-y-auto">
                  <div class="w-10 h-1 bg-line rounded-full mx-auto mb-4 sm:hidden"></div>
                  <h3 id="modal-confirm-title" class="text-xl font-bold tracking-tight text-ink">Konfirmasi</h3>
                  <p id="modal-confirm-message" class="mt-2 text-sm text-subtle leading-relaxed"></p>
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
          `);
          $('body').append(dynamicModal);
          modal = dynamicModal;
        }

        $('#modal-confirm-title').text(title || 'Konfirmasi');
        $('#modal-confirm-message').text(message);
        UI.openModal('modal-confirm');

        function cleanup(result) {
          $('#modal-confirm-ok').off('click.confirm');
          $('#modal-confirm-cancel').off('click.confirm');
          modal.find('[data-modal-dismiss]').off('click.confirm');
          $(document).off('keydown.confirm_esc');
          UI.closeModal('modal-confirm');
          resolve(result);
        }

        $('#modal-confirm-ok').one('click.confirm', function () {
          cleanup(true);
        });

        $('#modal-confirm-cancel, #modal-confirm [data-modal-dismiss]').one('click.confirm', function () {
          cleanup(false);
        });

        $(document).one('keydown.confirm_esc', function (e) {
          if (e.key === 'Escape' || e.keyCode === 27) {
            cleanup(false);
          }
        });
      });
    },

    /**
     * Buka modal dialog berdasarkan ID elemen.
     * @param {string} id
     */
    openModal: function (id) {
      const cleanId = id.replace(/^#/, '');
      const modal = $('#' + cleanId);
      if (modal.length) {
        modal.removeClass('hidden');
        $('body').addClass('overflow-hidden');
      }
    },

    /**
     * Tutup modal dialog berdasarkan ID elemen.
     * @param {string} id
     */
    closeModal: function (id) {
      const cleanId = id.replace(/^#/, '');
      const modal = $('#' + cleanId);
      if (modal.length) {
        modal.addClass('hidden');
        if ($('.fixed[role="dialog"]:not(.hidden)').length === 0) {
          $('body').removeClass('overflow-hidden');
        }
      }
    },

    /**
     * Pasang / lepas state loading pada tombol.
     * @param {HTMLElement|string|jQuery} button
     * @param {boolean} isLoading
     */
    setLoading: function (button, isLoading) {
      const $btn = $(button);
      if (!$btn.length) return;

      if (isLoading) {
        if (!$btn.data('original-html')) {
          $btn.data('original-html', $btn.html());
        }
        $btn.prop('disabled', true);
        $btn.addClass('opacity-60 cursor-not-allowed');
        $btn.html(`
          <span class="inline-flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span>Memproses...</span>
          </span>
        `);
      } else {
        const orig = $btn.data('original-html');
        if (orig) {
          $btn.html(orig);
        }
        $btn.prop('disabled', false);
        $btn.removeClass('opacity-60 cursor-not-allowed');
      }
    },

    /**
     * Escape string untuk mencegah XSS.
     * @param {any} text
     * @returns {string}
     */
    escape: function (text) {
      if (text === null || text === undefined) {
        return '';
      }
      return $('<div>').text(String(text)).html();
    },

    /**
     * Tampilkan state kosong (empty state) bersih sesuai prd.md §12.3.
     * @param {HTMLElement|string|jQuery} container
     * @param {string} [text='Tidak ada data.']
     * @param {string} [actionHtml='']
     */
    empty: function (container, text, actionHtml) {
      const $el = $(container);
      const msg = text || 'Tidak ada data.';
      const action = actionHtml ? `<div class="mt-4">${actionHtml}</div>` : '';
      $el.html(`
        <div class="py-16 text-center">
          <p class="text-sm font-medium text-subtle">${UI.escape(msg)}</p>
          ${action}
        </div>
      `);
    },

    /**
     * Tampilkan state error dengan pesan dan tombol aksi coba lagi.
     * @param {HTMLElement|string|jQuery} container
     * @param {string} [text='Terjadi kesalahan saat memuat data.']
     * @param {Function} [retryFn=null]
     */
    error: function (container, text, retryFn) {
      const $el = $(container);
      const msg = text || 'Terjadi kesalahan saat memuat data.';
      $el.html(`
        <div class="py-16 text-center">
          <p class="text-sm font-medium text-danger">${UI.escape(msg)}</p>
          ${retryFn ? `<button type="button" class="btn-retry mt-4 h-9 px-4 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors">Coba lagi</button>` : ''}
        </div>
      `);

      if (retryFn) {
        $el.find('.btn-retry').on('click', function () {
          retryFn();
        });
      }
    },

    /**
     * Hasilkan markup skeleton loader abu-abu (animate-pulse).
     * @param {number} [count=3]
     * @returns {string}
     */
    skeleton: function (count) {
      const c = count || 3;
      let html = '<div class="space-y-3 animate-pulse">';
      for (let i = 0; i < c; i++) {
        html += '<div class="h-16 rounded-xl bg-surface"></div>';
      }
      html += '</div>';
      return html;
    }
  };
})(jQuery);
