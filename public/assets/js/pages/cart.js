/**
 * Script Halaman Keranjang Pelanggan.
 * FR-CART-01..04, prd.md §6.6.
 *
 * Alur:
 * 1. Baca CartStore dari localStorage.
 * 2. Jika kosong → tampilkan state kosong.
 * 3. Sinkronkan harga & ketersediaan dengan server (api/menus/list.php?ids[]=...).
 * 4. Render baris item dengan stepper dan tombol hapus.
 * 5. Hitung subtotal. Item tidak tersedia memblokir tombol Checkout.
 */
$(function () {
  'use strict';

  /* ============================================================
     HELPERS
     ============================================================ */
  function formatRupiah(amount) {
    return (window.Fmt && typeof window.Fmt.rupiah === 'function')
      ? window.Fmt.rupiah(amount)
      : 'Rp ' + (parseInt(amount, 10) || 0).toLocaleString('id-ID');
  }

  const PLACEHOLDER = 'assets/img/placeholder-menu.svg';

  /* ============================================================
     STATE
     ============================================================ */
  // items: array {id, name, price, qty, image_url, image, unavailable?}
  let cartItems = [];

  /* ============================================================
     INISIALISASI: BACA localStorage LALU SINKRON SERVER
     ============================================================ */
  function init() {
    showState('loading');

    const stored = CartStore.getItems();

    if (!stored || stored.length === 0) {
      showState('empty');
      return;
    }

    // Salin ke state lokal (shallow copy agar tidak mutasi store)
    cartItems = stored.map(function (it) {
      return $.extend({}, it);
    });

    // Sinkronkan dengan server
    syncWithServer();
  }

  /**
   * Panggil api/menus/list.php?ids[]=... untuk memperbarui harga, nama,
   * dan ketersediaan. Setelah selesai, render halaman.
   */
  function syncWithServer() {
    const ids = cartItems.map(function (it) { return it.id; });

    // Bangun query string manual karena $.ajax data param tidak support array parameter berganda
    const params = ids.map(function (id) { return 'ids[]=' + encodeURIComponent(id); }).join('&');
    const url = (function () {
      const baseEl = document.querySelector('base');
      const base = baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
      return base + 'api/menus/list.php?' + params + '&include_unavailable=1&per_page=100';
    })();

    $.ajax({
      url: url,
      type: 'GET',
      dataType: 'json'
    })
    .done(function (res) {
      if (res && res.data && res.data.data) {
        const serverMenus = res.data.data;
        const serverMap = {};
        serverMenus.forEach(function (m) {
          serverMap[parseInt(m.id, 10)] = m;
        });

        cartItems.forEach(function (item) {
          const sm = serverMap[parseInt(item.id, 10)];
          if (sm) {
            // Perbarui harga, nama, image_url dari server
            item.name      = sm.name;
            item.price     = parseInt(sm.price, 10) || item.price;
            item.image_url = sm.image_url || item.image_url || null;
            item.unavailable = !sm.is_available;
          } else {
            // Tidak ditemukan di server — tandai tidak tersedia
            item.unavailable = true;
          }
        });
      }
      renderCart();
    })
    .fail(function () {
      // Jika gagal sinkron → render dengan data lokal, tanpa memblokir
      renderCart();
    });
  }

  /* ============================================================
     RENDER
     ============================================================ */
  function renderCart() {
    if (!cartItems.length) {
      showState('empty');
      return;
    }

    const $itemsContainer = $('#cart-items').empty();
    let hasUnavailable = false;

    cartItems.forEach(function (item) {
      if (item.unavailable) hasUnavailable = true;

      const imgSrc = item.image_url || item.image || PLACEHOLDER;
      const priceFormatted = formatRupiah(item.price);
      const lineTotal = formatRupiah((item.price || 0) * (item.qty || 1));
      const isUnavail = !!item.unavailable;

      const $row = $(`
        <div
          class="cart-item flex items-center gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-xl border ${isUnavail ? 'border-danger-light bg-danger-light/20' : 'border-line bg-white'} transition-colors"
          data-id="${item.id}"
        >
          <!-- Foto Kecil -->
          <div class="relative shrink-0">
            <img
              src="${$('<div>').text(imgSrc).html()}"
              alt="${$('<div>').text(item.name).html()}"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover bg-surface border border-line ${isUnavail ? 'opacity-50' : ''}"
              onerror="this.src='${PLACEHOLDER}'"
            >
            ${isUnavail ? '<span class="absolute -top-1.5 -right-1.5 text-[10px] font-bold bg-danger text-white px-1.5 py-0.5 rounded-full leading-none">Habis</span>' : ''}
          </div>

          <!-- Nama & Harga -->
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm sm:text-base text-ink leading-snug line-clamp-2">
              ${$('<div>').text(item.name).html()}
            </p>
            <p class="mt-0.5 text-xs text-subtle tabular-nums">${priceFormatted} / item</p>
            <p class="mt-1 text-sm font-bold text-ink tabular-nums">${lineTotal}</p>
            ${isUnavail ? '<p class="mt-1 text-xs text-danger font-medium">Tidak tersedia</p>' : ''}
          </div>

          <!-- Stepper + Hapus -->
          <div class="flex flex-col items-end gap-2 shrink-0">
            ${isUnavail ? `
              <!-- Tombol Hapus saja untuk item tidak tersedia -->
              <button
                type="button"
                class="btn-remove-item h-8 w-8 rounded-full border border-line text-danger flex items-center justify-center hover:bg-danger hover:text-white hover:border-danger transition-all cursor-pointer"
                data-id="${item.id}"
                title="Hapus"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </button>
            ` : `
              <!-- Stepper: −, jumlah, + -->
              <div class="flex items-center gap-1 bg-surface border border-line rounded-full p-0.5">
                <button
                  type="button"
                  class="btn-stepper-minus w-7 h-7 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
                  data-id="${item.id}"
                  ${item.qty <= 1 ? 'disabled' : ''}
                  title="Kurangi"
                >
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <span class="qty-label w-6 text-center text-xs font-bold text-ink tabular-nums" data-id="${item.id}">${item.qty}</span>
                <button
                  type="button"
                  class="btn-stepper-plus w-7 h-7 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
                  data-id="${item.id}"
                  ${item.qty >= 99 ? 'disabled' : ''}
                  title="Tambah"
                >
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
              </div>
              <!-- Tombol Hapus -->
              <button
                type="button"
                class="btn-remove-item text-xs text-subtle hover:text-danger transition-colors cursor-pointer"
                data-id="${item.id}"
              >
                Hapus
              </button>
            `}
          </div>
        </div>
      `);

      $itemsContainer.append($row);
    });

    // Banner tidak tersedia
    if (hasUnavailable) {
      $('#cart-unavailable-banner').removeClass('hidden');
    } else {
      $('#cart-unavailable-banner').addClass('hidden');
    }

    updateSummary();
    showState('content');
  }

  /* ============================================================
     SUMMARY (SUBTOTAL + STATUS CHECKOUT)
     ============================================================ */
  function updateSummary() {
    let subtotal = 0;
    let hasUnavailable = false;

    cartItems.forEach(function (item) {
      if (item.unavailable) {
        hasUnavailable = true;
      } else {
        subtotal += (parseInt(item.price, 10) || 0) * (parseInt(item.qty, 10) || 1);
      }
    });

    $('#cart-subtotal').text(formatRupiah(subtotal));

    const $btnCheckout = $('#btn-checkout');
    if (hasUnavailable) {
      $btnCheckout
        .addClass('opacity-40 pointer-events-none')
        .attr('aria-disabled', 'true')
        .attr('title', 'Hapus item tidak tersedia sebelum checkout');
    } else {
      $btnCheckout
        .removeClass('opacity-40 pointer-events-none')
        .removeAttr('aria-disabled')
        .removeAttr('title');
    }
  }

  /* ============================================================
     STATE UI
     ============================================================ */
  function showState(state) {
    $('#cart-loading').addClass('hidden');
    $('#cart-empty').addClass('hidden');
    $('#cart-content').addClass('hidden');

    if (state === 'loading') {
      $('#cart-loading').removeClass('hidden');
    } else if (state === 'empty') {
      $('#cart-empty').removeClass('hidden');
    } else if (state === 'content') {
      $('#cart-content').removeClass('hidden');
    }
  }

  /* ============================================================
     EVENT: STEPPER − (KURANGI)
     ============================================================ */
  $('#cart-items').on('click', '.btn-stepper-minus', function () {
    const id = parseInt($(this).data('id'), 10);
    const item = cartItems.find(function (it) { return parseInt(it.id, 10) === id; });
    if (!item) return;

    if (item.qty > 1) {
      item.qty--;
      CartStore.updateQty(id, item.qty);
      refreshRow(id, item);
    }
  });

  /* ============================================================
     EVENT: STEPPER + (TAMBAH)
     ============================================================ */
  $('#cart-items').on('click', '.btn-stepper-plus', function () {
    const id = parseInt($(this).data('id'), 10);
    const item = cartItems.find(function (it) { return parseInt(it.id, 10) === id; });
    if (!item) return;

    if (item.qty < 99) {
      item.qty++;
      CartStore.updateQty(id, item.qty);
      refreshRow(id, item);
    }
  });

  /**
   * Perbarui tampilan satu baris item (qty + line total + disable state stepper)
   * tanpa re-render seluruh daftar.
   */
  function refreshRow(id, item) {
    const $row = $('#cart-items .cart-item[data-id="' + id + '"]');
    $row.find('.qty-label[data-id="' + id + '"]').text(item.qty);
    $row.find('.btn-stepper-minus[data-id="' + id + '"]').prop('disabled', item.qty <= 1);
    $row.find('.btn-stepper-plus[data-id="' + id + '"]').prop('disabled', item.qty >= 99);

    // Perbarui line total
    const lineTotal = formatRupiah((item.price || 0) * item.qty);
    $row.find('.text-sm.font-bold.text-ink.tabular-nums').text(lineTotal);

    updateSummary();
  }

  /* ============================================================
     EVENT: HAPUS SATU ITEM
     ============================================================ */
  $('#cart-items').on('click', '.btn-remove-item', function () {
    const id = parseInt($(this).data('id'), 10);
    cartItems = cartItems.filter(function (it) { return parseInt(it.id, 10) !== id; });
    CartStore.remove(id);

    if (cartItems.length === 0) {
      showState('empty');
      return;
    }

    $('#cart-items .cart-item[data-id="' + id + '"]').fadeOut(200, function () {
      $(this).remove();
      updateSummary();
    });

    // Perbarui banner
    const hasUnavail = cartItems.some(function (it) { return !!it.unavailable; });
    if (!hasUnavail) {
      $('#cart-unavailable-banner').addClass('hidden');
    }
  });

  /* ============================================================
     EVENT: KOSONGKAN KERANJANG (konfirmasi modal)
     ============================================================ */
  $('#btn-clear-cart').on('click', function () {
    openClearModal();
  });

  $('#btn-clear-confirm').on('click', function () {
    CartStore.clear();
    cartItems = [];
    closeClearModal();
    showState('empty');
  });

  function openClearModal() {
    const $modal = $('#modal-clear-confirm');
    $modal.removeClass('hidden');
    // Tambah class animasi masuk
    $modal.find('[role="dialog"] > div:last-child, .rounded-t-2xl').addClass('animate-slide-up');
  }

  function closeClearModal() {
    $('#modal-clear-confirm').addClass('hidden');
  }

  // Tutup modal via backdrop atau tombol batal
  $('#modal-clear-confirm').on('click', '[data-modal-dismiss]', function () {
    closeClearModal();
  });

  /* ============================================================
     SINKRONISASI ANTAR TAB (event storage)
     ============================================================ */
  $(window).on('storage', function (e) {
    const orig = e.originalEvent || e;
    if (orig && orig.key === 'foodease_cart') {
      // Re-init saat localStorage berubah dari tab lain
      init();
    }
  });

  /* ============================================================
     MULAI
     ============================================================ */
  init();
});
