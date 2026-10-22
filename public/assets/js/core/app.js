/**
 * Konfigurasi global aplikasi & jQuery AJAX.
 */
(function ($) {
  'use strict';

  // Ambil token CSRF dari meta tag
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  // Setup default header untuk semua panggilan $.ajax
  if (csrfToken) {
    $.ajaxSetup({
      headers: {
        'X-CSRF-Token': csrfToken
      }
    });
  }

  // Handler AJAX global
  $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    const status = jqXHR.status;
    let message = 'Terjadi kesalahan pada server.';

    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
      message = jqXHR.responseJSON.message;
    }

    // 401 Unauthorized -> arahkan ke halaman login
    if (status === 401) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', 'Sesi berakhir. Mengalihkan ke login...');
      }
      setTimeout(function () {
        const baseUrl = document.querySelector('base')
          ? document.querySelector('base').href
          : '/foodease/';
        window.location.href = baseUrl + 'login.php';
      }, 1200);
      return;
    }

    // 403 Forbidden
    if (status === 403) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', message || 'Akses ditolak.');
      }
      return;
    }

    // 500 Server Error
    if (status >= 500) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', message || 'Terjadi kesalahan sistem (500).');
      }
    }
  });

})(jQuery);
