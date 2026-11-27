/**
 * Pengelola lokalisasi ringan di sisi client.
 * Mengambil kamus dari server (Single Source of Truth: app/lang/*.php via #app-i18n dan api/lang.php).
 */
(function ($) {
  'use strict';

  var STORAGE_KEY = 'foodease_lang';
  var SUPPORTED = ['id', 'en', 'zh'];

  var currentLang = document.documentElement.lang || 'id';
  var dict = {};

  // Muat kamus awal yang disisipkan PHP di <script id="app-i18n" type="application/json">
  (function hydrateFromServer() {
    var el = document.getElementById('app-i18n');
    if (!el) return;
    try {
      var parsed = JSON.parse(el.textContent || '{}');
      if (parsed.lang && SUPPORTED.indexOf(parsed.lang) !== -1) {
        currentLang = parsed.lang;
      }
      if (parsed.translations && typeof parsed.translations === 'object') {
        dict = parsed.translations;
      }
    } catch (e) {}
  })();

  var I18n = {
    getLang: function () {
      return currentLang;
    },

    t: function (key, fallback) {
      if (Object.prototype.hasOwnProperty.call(dict, key)) {
        return dict[key];
      }
      return fallback !== undefined ? fallback : key;
    },

    // Terjemahan katalog sudah dihitung oleh server sesuai bahasa aktif
    translateCategory: function (name) {
      return name || '';
    },

    translateMenuName: function (name) {
      return name || '';
    },

    translateMenuDesc: function (desc) {
      return desc || '';
    },

    setLang: function (lang, reload) {
      if (SUPPORTED.indexOf(lang) === -1) return;
      var self = this;

      currentLang = lang;
      document.documentElement.lang = lang;
      try {
        localStorage.setItem(STORAGE_KEY, lang);
      } catch (e) {}

      if (window.Api && typeof window.Api.post === 'function') {
        window.Api.post('api/lang.php', { lang: lang })
          .then(function (res) {
            if (res && res.data && res.data.translations) {
              dict = res.data.translations;
            }
            self.applyDOM();
            $(document).trigger('app:lang-changed', [lang]);
            if (reload === true) {
              window.location.reload();
            }
          })
          .catch(function () {
            self.applyDOM();
            $(document).trigger('app:lang-changed', [lang]);
          });
      } else {
        self.applyDOM();
        $(document).trigger('app:lang-changed', [lang]);
      }
    },

    applyDOM: function () {
      var self = this;
      $('[data-i18n]').each(function () {
        var key = $(this).attr('data-i18n');
        if (key && Object.prototype.hasOwnProperty.call(dict, key)) {
          $(this).text(self.t(key));
        }
      });
      $('[data-i18n-placeholder]').each(function () {
        var key = $(this).attr('data-i18n-placeholder');
        if (key && Object.prototype.hasOwnProperty.call(dict, key)) {
          $(this).attr('placeholder', self.t(key));
        }
      });

      $('#current-lang-label').text(currentLang.toUpperCase());

      $('.lang-option').each(function () {
        var l = $(this).data('lang');
        $(this).find('.lang-check').toggleClass('hidden', l !== currentLang);
      });

      $('[data-lang-switch] button[data-l]').each(function () {
        $(this).attr('aria-pressed', $(this).attr('data-l') === currentLang ? 'true' : 'false');
      });
    }
  };

  window.I18n = I18n;

  $(function () {
    I18n.applyDOM();

    $(document).on('click', '#btn-lang-toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $('#lang-dropdown-menu').toggleClass('hidden');
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('#lang-dropdown-wrapper').length) {
        $('#lang-dropdown-menu').addClass('hidden');
      }
    });

    $(document).on('click', '.lang-option', function (e) {
      e.preventDefault();
      var selected = $(this).data('lang');
      $('#lang-dropdown-menu').addClass('hidden');
      I18n.setLang(selected, false);
    });

    $(document).on('click', '[data-lang-switch] button[data-l]', function () {
      var lang = $(this).attr('data-l');
      if (!lang) return;
      I18n.setLang(lang, false);
    });
  });
})(jQuery);
