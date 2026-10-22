/**
 * Objek Icons: kumpulan ikon SVG inline outline (stroke 2) standar aplikasi.
 * Mendukung: search, cart, plus, minus, close, check, menu.
 */
window.Icons = (function () {
  'use strict';

  function svg(content, cls) {
    const c = cls || 'h-5 w-5';
    return `<svg class="${c}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${content}</svg>`;
  }

  return {
    search: function (cls) {
      return svg('<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>', cls);
    },
    cart: function (cls) {
      return svg('<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>', cls);
    },
    plus: function (cls) {
      return svg('<line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>', cls);
    },
    minus: function (cls) {
      return svg('<line x1="5" y1="12" x2="19" y2="12"></line>', cls);
    },
    close: function (cls) {
      return svg('<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>', cls);
    },
    check: function (cls) {
      return svg('<polyline points="20 6 9 17 4 12"></polyline>', cls);
    },
    menu: function (cls) {
      return svg('<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>', cls);
    },
    get: function (name, cls) {
      if (typeof this[name] === 'function') {
        return this[name](cls);
      }
      return '';
    }
  };
})();
