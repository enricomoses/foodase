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

  // Pengelola Tema Dark & Light Mode
  window.Theme = {
    isDark: function () {
      return document.documentElement.classList.contains('dark');
    },
    toggle: function () {
      const isCurrentlyDark = this.isDark();
      const newTheme = isCurrentlyDark ? 'light' : 'dark';
      this.set(newTheme);
      return newTheme;
    },
    set: function (theme) {
      var isDark = (theme === 'dark');
      if (isDark) {
        document.documentElement.classList.add('dark');
        try { localStorage.setItem('foodease_theme', 'dark'); } catch (e) {}
      } else {
        document.documentElement.classList.remove('dark');
        try { localStorage.setItem('foodease_theme', 'light'); } catch (e) {}
      }
      this._applyTheme(isDark);
      this.updateIcons();
      document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: theme } }));
    },
    _applyTheme: function (isDark) {
      // Direct DOM style manipulation agar menang atas Tailwind CDN runtime
      var body   = document.body;
      var aside  = document.querySelector('aside');
      var header = document.querySelector('header');
      var main   = document.querySelector('main');

      if (isDark) {
        if (body)   { body.style.setProperty('background-color', '#111111', 'important');   body.style.setProperty('color', '#f3f3f3', 'important'); }
        if (aside)  { aside.style.setProperty('background-color', '#161616', 'important');  aside.style.setProperty('border-color', '#2a2a2a', 'important'); }
        if (header) { header.style.setProperty('background-color', '#161616', 'important'); header.style.setProperty('border-color', '#2a2a2a', 'important'); }
        if (main)   { main.style.setProperty('background-color', '#111111', 'important'); }

        // Cards
        document.querySelectorAll('.bg-white').forEach(function (el) {
          el.style.setProperty('background-color', '#161616', 'important');
          el.style.setProperty('color', '#f3f3f3', 'important');
          el.style.setProperty('border-color', '#2a2a2a', 'important');
        });
        document.querySelectorAll('.bg-surface').forEach(function (el) {
          el.style.setProperty('background-color', '#1e1e1e', 'important');
        });
        // Text
        document.querySelectorAll('.text-ink').forEach(function (el) {
          el.style.setProperty('color', '#f3f3f3', 'important');
        });
        document.querySelectorAll('.text-subtle').forEach(function (el) {
          el.style.setProperty('color', '#909090', 'important');
        });
        document.querySelectorAll('.border-line').forEach(function (el) {
          el.style.setProperty('border-color', '#2a2a2a', 'important');
        });
        // Inputs
        document.querySelectorAll('input:not([type=checkbox]):not([type=radio]),select,textarea').forEach(function (el) {
          el.style.setProperty('background-color', '#1e1e1e', 'important');
          el.style.setProperty('color', '#f3f3f3', 'important');
          el.style.setProperty('border-color', '#333333', 'important');
        });
        // Nav links
        document.querySelectorAll('nav a').forEach(function (el) {
          el.style.setProperty('color', '#999999', 'important');
        });
        document.querySelectorAll('nav a.bg-surface, nav a.font-bold').forEach(function (el) {
          el.style.setProperty('background-color', '#2a2a2a', 'important');
          el.style.setProperty('color', '#ffffff', 'important');
        });
        // Table headers
        document.querySelectorAll('thead tr, tfoot tr').forEach(function (el) {
          el.style.setProperty('background-color', '#181818', 'important');
        });
      } else {
        // Reset to light mode - remove inline styles
        [body, aside, header, main].forEach(function (el) {
          if (el) { el.style.removeProperty('background-color'); el.style.removeProperty('color'); el.style.removeProperty('border-color'); }
        });
        document.querySelectorAll('.bg-white,.bg-surface,.text-ink,.text-subtle,.border-line,nav a,thead tr,tfoot tr').forEach(function (el) {
          el.style.removeProperty('background-color');
          el.style.removeProperty('color');
          el.style.removeProperty('border-color');
        });
        document.querySelectorAll('input:not([type=checkbox]):not([type=radio]),select,textarea').forEach(function (el) {
          el.style.removeProperty('background-color');
          el.style.removeProperty('color');
          el.style.removeProperty('border-color');
        });
      }
    },
    updateIcons: function () {
      const isDark = this.isDark();
      $('.btn-theme-toggle .icon-moon').toggleClass('hidden', isDark);
      $('.btn-theme-toggle .icon-sun').toggleClass('hidden', !isDark);
      $('.btn-theme-toggle').attr('title', isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap');
    }
  };

  // Inisialisasi ikon tema saat DOM siap
  $(function () {
    window.Theme.updateIcons();
    // Terapkan tema yang tersimpan saat halaman load
    var savedTheme = (function () {
      try { return localStorage.getItem('foodease_theme'); } catch (e) { return null; }
    })();
    var isDark = savedTheme === 'dark' ||
      (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
      window.Theme._applyTheme(true);
    }
    $(document).on('click', '.btn-theme-toggle', function (e) {
      e.preventDefault();
      window.Theme.toggle();
    });
  });

})(jQuery);

// ─── DARK MODE CSS INJECTOR ─────────────────────────────────────────────────
// Menggunakan MutationObserver agar style kita SELALU di akhir <head>
// sehingga menang dalam cascade meskipun Tailwind CDN inject style baru.
(function () {
  'use strict';

  var DARK_CSS = [
    'html.dark body{background-color:#111111!important;color:#f3f3f3!important}',
    'html.dark aside{background-color:#161616!important;border-right-color:#2a2a2a!important;border-color:#2a2a2a!important}',
    'html.dark header{background-color:#161616!important;border-bottom-color:#2a2a2a!important;border-color:#2a2a2a!important}',
    'html.dark main{background-color:#111111!important}',
    'html.dark .bg-white{background-color:#161616!important;color:#f3f3f3!important}',
    'html.dark .bg-surface{background-color:#1e1e1e!important;color:#e0e0e0!important}',
    'html.dark .border-line{border-color:#2a2a2a!important}',
    'html.dark .divide-line>:not([hidden])~:not([hidden]){border-color:#2a2a2a!important}',
    'html.dark .text-ink{color:#f3f3f3!important}',
    'html.dark .text-subtle{color:#909090!important}',
    'html.dark .hover\\:bg-surface:hover{background-color:#252525!important}',
    'html.dark .hover\\:bg-neutral-200:hover{background-color:#2a2a2a!important}',
    'html.dark .hover\\:bg-neutral-100:hover{background-color:#222222!important}',
    'html.dark input:not([type=checkbox]):not([type=radio]),html.dark select,html.dark textarea{background-color:#1e1e1e!important;border-color:#333!important;color:#f3f3f3!important}',
    'html.dark input:focus,html.dark select:focus,html.dark textarea:focus{border-color:#fff!important;background-color:#161616!important}',
    'html.dark .floating-label{color:#777!important}',
    'html.dark .animate-pulse{background-color:#252525!important}',
    'html.dark thead tr{background-color:#181818!important}',
    'html.dark tfoot tr{background-color:#181818!important}',
    'html.dark .bg-accent-light{background-color:rgba(6,193,103,.15)!important;color:#4ade80!important}',
    'html.dark .text-accent{color:#4ade80!important}',
    'html.dark .bg-danger-light{background-color:rgba(225,25,0,.15)!important;color:#fca5a5!important}',
    'html.dark .text-danger{color:#fca5a5!important}',
    'html.dark .border-danger{border-color:rgba(225,25,0,.4)!important}',
    'html.dark .btn-theme-toggle{background-color:#2a2a2a!important;color:#aaa!important}',
    'html.dark .btn-theme-toggle:hover{background-color:#383838!important;color:#fff!important}',
    'html.dark .rounded-full.bg-white{background-color:#2a2a2a!important}',
    'html.dark .shadow-sm{box-shadow:0 1px 3px rgba(0,0,0,.5)!important}',
    'html.dark nav a{color:#999!important}',
    'html.dark nav a.bg-surface,html.dark nav a.font-bold.text-ink{background-color:#2a2a2a!important;color:#fff!important}',
    'html.dark .bg-black\\/40{background-color:rgba(0,0,0,.8)!important}',
  ].join('');

  var _injected = false;
  var _observer = null;

  function ensureDarkStyleLast() {
    var existing = document.getElementById('fe-dark-inject');
    var lastStyle = document.head.lastElementChild;
    if (!existing) {
      var s = document.createElement('style');
      s.id = 'fe-dark-inject';
      s.textContent = DARK_CSS;
      document.head.appendChild(s);
      _injected = true;
    } else if (lastStyle !== existing) {
      // Bukan yang terakhir, pindahkan ke akhir
      document.head.appendChild(existing);
    }
  }

  function startObserver() {
    if (_observer) return;
    _observer = new MutationObserver(function (mutations) {
      var needsReorder = false;
      for (var i = 0; i < mutations.length; i++) {
        var m = mutations[i];
        if (m.addedNodes.length > 0) {
          needsReorder = true;
          break;
        }
      }
      if (needsReorder) {
        var existing = document.getElementById('fe-dark-inject');
        if (existing && document.head.lastElementChild !== existing) {
          // Jeda singkat agar Tailwind selesai inject semua stylenya
          setTimeout(ensureDarkStyleLast, 50);
        }
      }
    });
    _observer.observe(document.head, { childList: true });
  }

  // Jalankan setelah DOM siap
  function init() {
    ensureDarkStyleLast();
    startObserver();
    // Pastikan lagi setelah Tailwind CDN selesai (biasanya ~300ms)
    setTimeout(ensureDarkStyleLast, 300);
    setTimeout(ensureDarkStyleLast, 800);
    setTimeout(ensureDarkStyleLast, 2000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-inject tiap kali tema berubah
  document.addEventListener('themechange', function () {
    setTimeout(ensureDarkStyleLast, 100);
    setTimeout(ensureDarkStyleLast, 400);
  });
})();
