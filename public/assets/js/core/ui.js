/**
 * Komponen & Utility Antarmuka Pengguna (UI).
 */
window.UI = (function ($) {
  'use strict';

  // Inisialisasi event listener global untuk menutup modal dengan tombol Escape dan klik latar
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
     * Tampilkan notifikasi toast datar & bersih.
     * @param {'success'|'error'|'info'|'warning'} type
     * @param {string} message
     */
    toast: function (type, message) {
      let container = $('#toast-container');
      if (!container.length) {
        container = $('<div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none" aria-live="polite"></div>');
        $('body').append(container);
      }

      let borderColor = 'border-stone-300';
      let dotColor = 'bg-stone-400';

      if (type === 'success') {
        borderColor = 'border-emerald-300';
        dotColor = 'bg-emerald-600';
      } else if (type === 'error') {
        borderColor = 'border-red-300';
        dotColor = 'bg-red-600';
      } else if (type === 'warning') {
        borderColor = 'border-amber-300';
        dotColor = 'bg-amber-600';
      }

      const toast = $(`
        <div class="pointer-events-auto flex items-start gap-2.5 rounded-md border ${borderColor} bg-white p-3 shadow-sm text-sm text-stone-800 transition-opacity duration-200 cursor-pointer">
          <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full ${dotColor}"></span>
          <div class="flex-1 leading-snug">${UI.escape(message)}</div>
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
     * Dialog konfirmasi berbasis Promise.
     * @param {string} message
     * @returns {Promise<boolean>}
     */
    confirm: function (message) {
      return new Promise(function (resolve) {
        const modal = $('#modal-confirm');
        if (!modal.length) {
          // Fallback native jika markup modal belum terpasang
          resolve(window.confirm(message));
          return;
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
        $btn.addClass('opacity-75 cursor-not-allowed');
        $btn.html(`
          <span class="inline-flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
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
        $btn.removeClass('opacity-75 cursor-not-allowed');
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
     * Tampilkan state kosong (empty state) datar pada kontainer.
     * @param {HTMLElement|string|jQuery} container
     * @param {string} [text='Tidak ada data.']
     */
    empty: function (container, text) {
      const $el = $(container);
      const msg = text || 'Tidak ada data.';
      $el.html(`
        <div class="py-12 text-center text-stone-500 text-sm">
          <p>${UI.escape(msg)}</p>
        </div>
      `);
    }
  };
})(jQuery);
