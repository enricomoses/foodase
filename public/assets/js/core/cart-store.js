/**
 * CartStore — Pengelola penyimpanan keranjang belanja berbasis localStorage.
 * Standar: FR-CART-01..04.
 */
window.CartStore = (function ($) {
  'use strict';

  const STORAGE_KEY = 'foodease_cart';

  function readCart() {
    try {
      const data = localStorage.getItem(STORAGE_KEY);
      return data ? JSON.parse(data) : [];
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
    const items = readCart();
    const totalCount = items.reduce(function (sum, item) {
      return sum + (parseInt(item.qty, 10) || 1);
    }, 0);
    $('#cart-count').text(totalCount);
  }

  // Update badge saat halaman selesai dimuat
  $(function () {
    updateBadge();
  });

  return {
    /**
     * Dapatkan semua item di keranjang.
     * @returns {Array}
     */
    getItems: function () {
      return readCart();
    },

    /**
     * Dapatkan total jumlah item (sum of quantities).
     * @returns {number}
     */
    count: function () {
      const items = readCart();
      return items.reduce(function (sum, item) {
        return sum + (parseInt(item.qty, 10) || 1);
      }, 0);
    },

    /**
     * Tambahkan menu ke keranjang dengan jumlah tertentu.
     * @param {object} menu {id, name, price, image, image_url, category_name}
     * @param {number} [qty=1]
     * @returns {Array}
     */
    add: function (menu, qty) {
      qty = parseInt(qty, 10) || 1;
      if (qty <= 0) return readCart();

      const items = readCart();
      const menuId = parseInt(menu.id, 10);
      const existing = items.find(function (it) {
        return parseInt(it.id, 10) === menuId;
      });

      if (existing) {
        existing.qty = (parseInt(existing.qty, 10) || 0) + qty;
      } else {
        items.push({
          id: menuId,
          name: menu.name,
          price: parseInt(menu.price, 10) || 0,
          image: menu.image || null,
          image_url: menu.image_url || null,
          category_name: menu.category_name || '',
          qty: qty
        });
      }

      writeCart(items);
      return items;
    },

    /**
     * Hapus satu item dari keranjang.
     * @param {number} menuId
     * @returns {Array}
     */
    remove: function (menuId) {
      menuId = parseInt(menuId, 10);
      let items = readCart();
      items = items.filter(function (it) {
        return parseInt(it.id, 10) !== menuId;
      });
      writeCart(items);
      return items;
    },

    /**
     * Ubah jumlah item tertentu.
     * @param {number} menuId
     * @param {number} qty
     * @returns {Array}
     */
    updateQty: function (menuId, qty) {
      menuId = parseInt(menuId, 10);
      qty = parseInt(qty, 10);
      if (qty <= 0) {
        return this.remove(menuId);
      }

      const items = readCart();
      const item = items.find(function (it) {
        return parseInt(it.id, 10) === menuId;
      });

      if (item) {
        item.qty = qty;
        writeCart(items);
      }
      return items;
    },

    /**
     * Kosongkan keranjang.
     */
    clear: function () {
      writeCart([]);
    },

    /**
     * Sinkronkan badge keranjang pada navbar.
     */
    syncBadge: function () {
      updateBadge();
    }
  };
})(jQuery);
