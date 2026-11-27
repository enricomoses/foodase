/**
 * CartStore — Pengelola keranjang belanja berbasis localStorage (FR-CART-01..04, BR-03).
 */
window.CartStore = (function ($) {
  'use strict';

  var STORAGE_KEY = 'foodease_cart';
  var MAX_QTY = 99;
  var MAX_ITEM_KIND = 30;

  function readCart() {
    try {
      var data = localStorage.getItem(STORAGE_KEY)
        || localStorage.getItem('nola_cart')
        || localStorage.getItem('kopinusa_cart');
      var parsed = data ? JSON.parse(data) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function writeCart(items) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    } catch (e) {}
    updateBadge();
  }

  function updateBadge() {
    var items = readCart();
    var totalCount = items.reduce(function (sum, item) {
      return sum + (parseInt(item.qty, 10) || 1);
    }, 0);
    $('#cart-count, .cart-badge-count').text(totalCount);
  }

  $(function () {
    updateBadge();
  });

  return {
    MAX_QTY: MAX_QTY,
    MAX_ITEM_KIND: MAX_ITEM_KIND,

    getItems: function () {
      return readCart();
    },

    count: function () {
      return readCart().reduce(function (sum, item) {
        return sum + (parseInt(item.qty, 10) || 1);
      }, 0);
    },

    add: function (menu, qty) {
      qty = parseInt(qty, 10) || 1;
      if (qty <= 0) return readCart();

      var items = readCart();
      var menuId = parseInt(menu.id, 10);
      var existing = items.find(function (it) {
        return parseInt(it.id, 10) === menuId;
      });

      if (existing) {
        var nextQty = Math.min(MAX_QTY, (parseInt(existing.qty, 10) || 0) + qty);
        existing.qty = nextQty;
      } else {
        if (items.length >= MAX_ITEM_KIND) {
          if (window.UI && typeof window.UI.toast === 'function') {
            window.UI.toast('warning', 'Maksimal ' + MAX_ITEM_KIND + ' jenis menu per pesanan.');
          }
          return items;
        }
        items.push({
          id: menuId,
          name: menu.name,
          price: parseInt(menu.price, 10) || 0,
          image: menu.image || null,
          image_url: menu.image_url || null,
          category_name: menu.category_name || '',
          qty: Math.min(MAX_QTY, qty)
        });
      }

      writeCart(items);
      return items;
    },

    remove: function (menuId) {
      menuId = parseInt(menuId, 10);
      var items = readCart().filter(function (it) {
        return parseInt(it.id, 10) !== menuId;
      });
      writeCart(items);
      return items;
    },

    updateQty: function (menuId, qty) {
      menuId = parseInt(menuId, 10);
      qty = parseInt(qty, 10);
      if (qty <= 0) {
        return this.remove(menuId);
      }

      var items = readCart();
      var item = items.find(function (it) {
        return parseInt(it.id, 10) === menuId;
      });

      if (item) {
        item.qty = Math.min(MAX_QTY, qty);
        writeCart(items);
      }
      return items;
    },

    clear: function () {
      writeCart([]);
    },

    syncBadge: function () {
      updateBadge();
    }
  };
})(jQuery);
