/**
 * Konfigurasi global aplikasi, tema dark/light, flash toast, dan logout konfirmasi.
 */
(function ($) {
  'use strict';

  var THEME_KEY = 'foodease_theme';

  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  if (csrfToken) {
    $.ajaxSetup({
      headers: { 'X-CSRF-Token': csrfToken }
    });
  }

  // Handler error AJAX global
  $(document).ajaxError(function (event, jqXHR) {
    var status = jqXHR.status;
    var message = (jqXHR.responseJSON && jqXHR.responseJSON.message)
      ? jqXHR.responseJSON.message
      : 'Terjadi kesalahan pada server.';

    if (status === 401) {
      // Jangan redirect saat di halaman login itu sendiri
      if (!window.location.pathname.endsWith('login.php')) {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('error', 'Sesi berakhir. Mengalihkan ke login...');
        }
        setTimeout(function () {
          var baseUrl = window.Api ? window.Api.baseUrl() : '/';
          window.location.href = baseUrl + 'login.php';
        }, 1200);
      }
      return;
    }

    if (status === 403 && window.UI && typeof window.UI.toast === 'function') {
      window.UI.toast('error', message || 'Akses ditolak.');
      return;
    }

    if (status >= 500 && window.UI && typeof window.UI.toast === 'function') {
      window.UI.toast('error', message || 'Terjadi kesalahan sistem (500).');
    }
  });

  // Pengelola Tema Dark & Light Mode (berbasis class html.dark — bersih tanpa inline style)
  window.Theme = {
    isDark: function () {
      return document.documentElement.classList.contains('dark');
    },
    toggle: function () {
      var next = this.isDark() ? 'light' : 'dark';
      this.set(next);
      return next;
    },
    set: function (theme) {
      var dark = theme === 'dark';
      document.documentElement.classList.toggle('dark', dark);
      try {
        localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light');
      } catch (e) {}
      this.updateIcons();
      document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: dark ? 'dark' : 'light' } }));
    },
    updateIcons: function () {
      var dark = this.isDark();
      $('.btn-theme-toggle .icon-moon').toggleClass('hidden', dark);
      $('.btn-theme-toggle .icon-sun').toggleClass('hidden', !dark);
      $('.btn-theme-toggle').attr('title', dark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap');
    }
  };

  $(function () {
    window.Theme.updateIcons();

    $(document).on('click', '.btn-theme-toggle', function (e) {
      e.preventDefault();
      window.Theme.toggle();
    });

    // Tampilkan flash toast dari sesi PHP jika tersedia
    var $flash = $('#php-flash-toast');
    if ($flash.length && window.UI && typeof window.UI.toast === 'function') {
      var flashMsg = $flash.data('message') || '';
      if (flashMsg) {
        setTimeout(function () {
          window.UI.toast($flash.data('type') || 'info', flashMsg, $flash.data('title') || '');
        }, 200);
      }
    }

    // Konfirmasi logout menggunakan UI.confirm
    $(document).on('click', '#btn-header-logout, [data-action="logout"]', function (e) {
      e.preventDefault();
      var $form = $(this).closest('form');
      if (!$form.length) {
        $form = $('#form-logout');
      }

      var proceed = function () {
        if ($form.length) {
          $form[0].submit();
        } else {
          var baseUrl = window.Api ? window.Api.baseUrl() : '/';
          window.location.href = baseUrl + 'logout.php';
        }
      };

      if (window.UI && typeof window.UI.confirm === 'function') {
        window.UI.confirm('Apakah Anda yakin ingin keluar dari sistem?', 'Konfirmasi Keluar')
          .then(function (confirmed) {
            if (confirmed) proceed();
          });
      } else {
        proceed();
      }
    });
  });
})(jQuery);
