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
     * Tampilkan notifikasi toast kapsul hitam di tengah bawah (prd.md §12.3).
     * @param {'success'|'error'|'info'|'warning'} type
     * @param {string} message
     */
    toast: function (type, message) {
      let container = $('#toast-container');
      if (!container.length) {
        container = $('<div id="toast-container" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center gap-2 w-auto max-w-sm px-4 pointer-events-none" aria-live="polite"></div>');
        $('body').append(container);
      }

      let dotColor = 'bg-white/60';
      if (type === 'success') {
        dotColor = 'bg-accent';
      } else if (type === 'error') {
        dotColor = 'bg-danger';
      } else if (type === 'warning') {
        dotColor = 'bg-white';
      }

      const toast = $(`
        <div class="pointer-events-auto flex items-center gap-2.5 rounded-full bg-black text-white px-5 py-2.5 text-sm font-medium shadow-lg transition-all duration-200 cursor-pointer">
          <span class="h-2 w-2 shrink-0 rounded-full ${dotColor}"></span>
          <span class="leading-tight">${UI.escape(message)}</span>
        </div>
      `);

      toast.on('click', function () {
        toast.fadeOut(150, function () {
          toast.remove();
        });
      });

      container.append(toast);

      setTimeout(function () {
        toast.fadeOut(200, function () {
          toast.remove();
        });
      }, 3500);
    },

    /**
     * Dialog konfirmasi berbasis Promise (modal/bottom sheet).
     * @param {string} message
     * @param {string} [title='Konfirmasi']
     * @returns {Promise<boolean>}
     */
    confirm: function (message, title) {
      return new Promise(function (resolve) {
        const modal = $('#modal-confirm');
        if (!modal.length) {
          resolve(window.confirm(message));
          return;
        }

        if (title) {
          $('#modal-confirm-title').text(title);
        } else {
          $('#modal-confirm-title').text('Konfirmasi');
        }

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
