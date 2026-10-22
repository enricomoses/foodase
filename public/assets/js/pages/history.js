/**
 * Script Halaman Riwayat Pesanan Pelanggan.
 * FR-TRK-03, prd.md §6.8, §12.3, §12.4.
 *
 * Fitur:
 * 1. Mengambil daftar pesanan dari session browser lewat api/orders/mine.php.
 * 2. Menampilkan kartu pesanan dengan statusBadge, format tanggal, dan total.
 * 3. Tautan langsung menuju halaman pelacakan track.php?code=...
 */
$(function () {
  'use strict';

  function getBaseUrl() {
    const baseEl = document.querySelector('base');
    return baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
  }

  function init() {
    loadMyOrders();

    $('#btn-history-retry').on('click', function () {
      loadMyOrders();
    });
  }

  function loadMyOrders() {
    showLoading(true);
    $('#history-error').addClass('hidden');
    $('#history-empty').addClass('hidden');
    $('#history-list').addClass('hidden').empty();

    $.ajax({
      url: getBaseUrl() + 'api/orders/mine.php',
      type: 'GET',
      dataType: 'json'
    })
    .done(function (res) {
      showLoading(false);
      const orders = (res && res.data && Array.isArray(res.data)) ? res.data : [];

      if (!orders.length) {
        $('#history-empty').removeClass('hidden');
        return;
      }

      renderOrdersList(orders);
    })
    .fail(function (jqXHR) {
      showLoading(false);
      const res = jqXHR.responseJSON;
      const msg = (res && res.message) ? res.message : 'Gagal memuat riwayat pesanan.';
      $('#history-error-text').text(msg);
      $('#history-error').removeClass('hidden');
    });
  }

  function renderOrdersList(orders) {
    const $container = $('#history-list').empty();

    orders.forEach(function (order) {
      const code = $('<div>').text(order.order_code).html();
      const name = $('<div>').text(order.customer_name).html();
      const dateStr = window.Fmt.datetime(order.created_at);
      const totalStr = window.Fmt.rupiah(order.total);

      let typeText = order.order_type === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang';
      if (order.order_type === 'dine_in' && order.table_number) {
        typeText += ' • Meja ' + order.table_number;
      }

      // Ringkasan item
      let itemsSummary = '';
      if (order.items && order.items.length) {
        const itemNames = order.items.map(function (it) {
          return $('<div>').text(it.menu_name).html() + ' × ' + it.qty;
        });
        itemsSummary = itemNames.join(', ');
      } else {
        itemsSummary = (order.items_count || 1) + ' item';
      }

      const trackUrl = getBaseUrl() + 'track.php?code=' + encodeURIComponent(order.order_code);

      const $card = $(`
        <div class="rounded-2xl border border-line bg-white p-5 hover:border-black/30 transition-colors">
          <!-- Baris 1: Kode, Tanggal, Badge Status -->
          <div class="flex items-start justify-between gap-2 mb-2">
            <div>
              <a href="${trackUrl}" class="text-base font-bold text-ink hover:underline font-mono tabular-nums">
                ${code}
              </a>
              <p class="text-xs text-subtle mt-0.5">${dateStr} &bull; ${name}</p>
            </div>
            <div class="shrink-0">
              ${window.Fmt.statusBadge(order.status)}
            </div>
          </div>

          <!-- Baris 2: Tipe Pesanan & Status Bayar -->
          <div class="flex items-center gap-2 mb-3 text-xs text-subtle">
            <span class="inline-flex items-center rounded-md bg-surface px-2 py-0.5 font-medium text-ink">
              ${typeText}
            </span>
            <span>&bull;</span>
            <span>${window.Fmt.statusBadge(order.payment_status)}</span>
          </div>

          <!-- Baris 3: Ringkasan Item Menu -->
          <div class="bg-surface rounded-xl p-3 mb-4 text-xs text-ink/80 leading-relaxed line-clamp-2">
            ${itemsSummary}
          </div>

          <!-- Baris 4: Total & Tombol Lacak -->
          <div class="flex items-center justify-between pt-3 border-t border-line">
            <div>
              <p class="text-[11px] font-medium text-subtle">Total</p>
              <p class="text-base font-bold text-ink tabular-nums">${totalStr}</p>
            </div>
            <a
              href="${trackUrl}"
              class="h-9 px-4 rounded-full bg-black text-white text-xs font-medium inline-flex items-center gap-1.5 hover:bg-neutral-800 transition-colors"
            >
              <span>Lacak Pesanan</span>
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </a>
          </div>
        </div>
      `);

      $container.append($card);
    });

    $container.removeClass('hidden');
  }

  function showLoading(show) {
    if (show) {
      $('#history-loading').removeClass('hidden');
      $('#history-list').addClass('hidden');
    } else {
      $('#history-loading').addClass('hidden');
    }
  }

  init();
});
