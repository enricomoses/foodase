/**
 * Script Halaman Pelacakan Pesanan Pelanggan.
 * FR-TRK-01, FR-TRK-02, FR-TRK-04, prd.md §6.8, §12.3, §12.4.
 *
 * Fitur:
 * 1. Menampilkan status pesanan dengan judul dinamis & progress bar 4 segmen.
 * 2. Tampilan khusus bila pesanan dibatalkan (banner merah).
 * 3. Polling status otomatis tiap 10 detik, berhenti saat document.hidden dan status selesai/batal.
 * 4. Form pencarian kode + nama pemesan.
 * 5. Menggunakan Fmt.statusBadge, Fmt.rupiah, Fmt.datetime.
 */
$(function () {
  'use strict';

  /* ============================================================
     HELPERS & STATE
     ============================================================ */
  const PLACEHOLDER = 'assets/img/placeholder-menu.svg';

  function getBaseUrl() {
    const baseEl = document.querySelector('base');
    return baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
  }

  let currentCode    = '';
  let currentName    = '';
  let pollTimer      = null;
  let isPollingActive = false;
  let lastStatus     = null;

  /* ============================================================
     INISIALISASI
     ============================================================ */
  function init() {
    const urlParams = new URLSearchParams(window.location.search);
    const codeParam = urlParams.get('code') || $('#track-page').data('initial-code') || '';
    const nameParam = urlParams.get('name') || '';

    if (codeParam) {
      currentCode = codeParam.trim();
      currentName = nameParam.trim();
      $('#input-search-code').val(currentCode);
      if (currentName) {
        $('#input-search-name').val(currentName);
      }
      fetchOrderStatus(currentCode, currentName, false);
    }

    bindEvents();
    setupVisibilityWatcher();
  }

  /* ============================================================
     EVENT LISTENERS
     ============================================================ */
  function bindEvents() {
    // Submit form cari pesanan
    $('#form-track-search').on('submit', function (e) {
      e.preventDefault();
      const code = $('#input-search-code').val().trim();
      const name = $('#input-search-name').val().trim();

      $('#search-error-msg').addClass('hidden').text('');

      if (!code) {
        $('#search-error-msg').removeClass('hidden').text('Kode pesanan wajib diisi.');
        return;
      }

      currentCode = code;
      currentName = name;

      // Update URL di browser tanpa reload
      const newUrl = 'track.php?code=' + encodeURIComponent(code) +
        (name ? '&name=' + encodeURIComponent(name) : '');
      window.history.pushState({ code: code, name: name }, '', newUrl);

      fetchOrderStatus(currentCode, currentName, false);
    });

    // Tombol buka/tutup form cari lain
    $('#btn-toggle-search').on('click', function () {
      $('#track-search-card').toggleClass('hidden');
      if (!$('#track-search-card').hasClass('hidden')) {
        $('#input-search-code').focus();
      }
    });

    // Tombol cari ulang dari state error
    $('#btn-retry-search').on('click', function () {
      $('#track-error').addClass('hidden');
      $('#track-search-card').removeClass('hidden');
      $('#input-search-code').focus();
    });

    // Tombol refresh manual
    $('#btn-manual-refresh').on('click', function () {
      if (currentCode) {
        fetchOrderStatus(currentCode, currentName, true);
      }
    });
  }

  /* ============================================================
     PENGAMBILAN DATA STATUS (AJAX)
     ============================================================ */
  function fetchOrderStatus(code, name, isBackground) {
    if (!isBackground) {
      showLoading(true);
      $('#track-error').addClass('hidden');
    }

    const params = { code: code };
    if (name) {
      params.name = name;
    }

    $.ajax({
      url: getBaseUrl() + 'api/orders/track.php',
      type: 'GET',
      data: params,
      dataType: 'json'
    })
    .done(function (res) {
      showLoading(false);
      if (res && res.success && res.data) {
        renderOrderData(res.data);
      } else {
        showError(res.message || 'Pesanan tidak ditemukan.');
      }
    })
    .fail(function (jqXHR) {
      showLoading(false);
      const res = jqXHR.responseJSON;
      const msg = (res && res.message) ? res.message : 'Pesanan tidak ditemukan.';
      showError(msg);
    });
  }

  /* ============================================================
     RENDER TAMPILAN STATUS
     ============================================================ */
  function renderOrderData(order) {
    lastStatus = order.status;

    // Sembunyikan form pencarian jika sudah berhasil memuat
    $('#track-search-card').addClass('hidden');
    $('#track-result-section').removeClass('hidden');
    $('#track-error').addClass('hidden');
    $('#track-content').removeClass('hidden');

    // 1. Judul dan Subtitle Dinamis
    applyDynamicHeaders(order.status, order.payment_status);

    // 2. Kode Pesanan & Badge Status
    $('#track-order-code').text(order.order_code);
    $('#track-status-badge-container').html(window.Fmt.statusBadge(order.status));

    // 3. Progress Bar 4 Segmen
    renderProgressBar(order.status);

    // 4. Banner Pembatalan jika status 'cancelled'
    if (order.status === 'cancelled') {
      $('#track-cancelled-banner').removeClass('hidden');
      const reason = order.cancel_reason ? order.cancel_reason : 'Tidak ada keterangan';
      $('#track-cancel-reason').text('Alasan: ' + reason);
    } else {
      $('#track-cancelled-banner').addClass('hidden');
    }

    // 5. Info Pesanan
    $('#track-customer-name').text(order.customer_name);

    let typeText = order.order_type === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang';
    if (order.order_type === 'dine_in' && order.table_number) {
      typeText += ' (Meja ' + order.table_number + ')';
    }
    $('#track-order-type').text(typeText);

    $('#track-created-at').text(window.Fmt.datetime(order.created_at));
    $('#track-pay-badge').html(window.Fmt.statusBadge(order.payment_status));

    // Catatan
    if (order.notes && order.notes.trim() !== '') {
      $('#track-notes').text(order.notes);
      $('#track-notes-container').removeClass('hidden');
    } else {
      $('#track-notes-container').addClass('hidden');
    }

    // 6. Rincian Item
    renderItemsList(order.items || []);

    // 7. Total Pembayaran
    $('#track-total-amount').text(window.Fmt.rupiah(order.total));

    // 8. Kelola Auto-Refresh Polling (10 detik)
    managePolling(order.status);
  }

  /* ============================================================
     JUDUL & SUBTITLE DINAMIS
     ============================================================ */
  function applyDynamicHeaders(status, payStatus) {
    let title = '';
    let subtitle = '';

    switch (status) {
      case 'pending':
        if (payStatus === 'unpaid') {
          title = 'Menunggu pembayaran di kasir';
          subtitle = 'Tunjukkan kode pesanan kepada kasir untuk melakukan pembayaran.';
        } else {
          title = 'Menunggu konfirmasi';
          subtitle = 'Pesanan Anda sedang dalam antrean.';
        }
        break;

      case 'processing':
        title = 'Pesanan sedang disiapkan';
        subtitle = 'Pesanan Anda sudah dibayar dan sedang dipersiapkan oleh tim dapur.';
        break;

      case 'ready':
        title = 'Pesanan siap diambil';
        subtitle = 'Pesanan Anda telah selesai disiapkan! Silakan ambil di konter.';
        break;

      case 'completed':
        title = 'Pesanan telah selesai';
        subtitle = 'Terima kasih telah berkunjung dan menikmati sajian FoodEase.';
        break;

      case 'cancelled':
        title = 'Pesanan dibatalkan';
        subtitle = 'Pesanan ini telah dibatalkan.';
        break;

      default:
        title = 'Status Pesanan';
        subtitle = 'Memantau perkembangan pesanan Anda.';
    }

    $('#track-main-title').text(title);
    $('#track-subtitle').text(subtitle);
  }

  /* ============================================================
     PROGRESS BAR 4 SEGMEN (FR-TRK-01)
     Segmen: 1: Menunggu bayar, 2: Diproses, 3: Siap, 4: Selesai
     ============================================================ */
  function renderProgressBar(status) {
    if (status === 'cancelled') {
      $('#track-progress-wrapper').addClass('hidden');
      return;
    }

    $('#track-progress-wrapper').removeClass('hidden');

    // Tentukan segmen terisi (1-4)
    let fillLevel = 1;
    if (status === 'pending') {
      fillLevel = 1;
    } else if (status === 'processing') {
      fillLevel = 2;
    } else if (status === 'ready') {
      fillLevel = 3;
    } else if (status === 'completed') {
      fillLevel = 4;
    }

    for (let i = 1; i <= 4; i++) {
      const $bar   = $('#p-bar-' + i);
      const $label = $('#p-label-' + i);

      if (i <= fillLevel) {
        $bar.removeClass('bg-line').addClass('bg-black');
        $label.removeClass('text-subtle').addClass('text-ink font-bold');
      } else {
        $bar.removeClass('bg-black').addClass('bg-line');
        $label.removeClass('text-ink font-bold').addClass('text-subtle font-medium');
      }
    }
  }

  /* ============================================================
     RENDER ITEM DAFTAR MENU
     ============================================================ */
  function renderItemsList(items) {
    const $container = $('#track-items-list').empty();

    if (!items.length) {
      $container.html('<p class="text-xs text-subtle py-2">Tidak ada item.</p>');
      return;
    }

    items.forEach(function (item) {
      const imgSrc = item.image_url || PLACEHOLDER;
      const $row = $(`
        <div class="flex items-center gap-3 py-2.5">
          <img
            src="${$('<div>').text(imgSrc).html()}"
            alt="${$('<div>').text(item.menu_name).html()}"
            class="w-10 h-10 rounded-lg object-cover bg-surface border border-line shrink-0"
            onerror="this.src='${PLACEHOLDER}'"
          >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-ink line-clamp-1">${$('<div>').text(item.menu_name).html()}</p>
            <p class="text-xs text-subtle tabular-nums">${window.Fmt.rupiah(item.price)} × ${item.qty}</p>
          </div>
          <p class="text-sm font-bold text-ink tabular-nums shrink-0">${window.Fmt.rupiah(item.subtotal)}</p>
        </div>
      `);
      $container.append($row);
    });
  }

  /* ============================================================
     AUTO-REFRESH POLLING (FR-TRK-02)
     Tiap 10 detik, berhenti saat completed/cancelled atau tab hidden
     ============================================================ */
  function managePolling(status) {
    stopPolling();

    // Jika pesanan sudah completed atau cancelled, hentikan polling permanen
    if (status === 'completed' || status === 'cancelled') {
      $('#poll-indicator').addClass('hidden');
      return;
    }

    // Tampilkan indikator polling live
    $('#poll-indicator').removeClass('hidden').addClass('flex');
    startPolling();
  }

  function startPolling() {
    if (pollTimer) clearInterval(pollTimer);
    isPollingActive = true;

    pollTimer = setInterval(function () {
      if (document.hidden) {
        return; // Jangan refresh jika tab sedang di latar belakang
      }
      if (currentCode) {
        fetchOrderStatus(currentCode, currentName, true);
      }
    }, 10000); // 10 detik
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
    isPollingActive = false;
  }

  /* ============================================================
     VISIBILITY CHANGE WATCHER
     ============================================================ */
  function setupVisibilityWatcher() {
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden && currentCode && lastStatus !== 'completed' && lastStatus !== 'cancelled') {
        // Tab aktif kembali: segera refresh sekali dan hidupkan polling
        fetchOrderStatus(currentCode, currentName, true);
        startPolling();
      } else if (document.hidden) {
        stopPolling();
      }
    });
  }

  /* ============================================================
     STATE UI (LOADING & ERROR)
     ============================================================ */
  function showLoading(show) {
    if (show) {
      $('#track-loading').removeClass('hidden');
      $('#track-content').addClass('hidden');
      $('#track-error').addClass('hidden');
    } else {
      $('#track-loading').addClass('hidden');
    }
  }

  function showError(msg) {
    stopPolling();
    $('#poll-indicator').addClass('hidden');
    $('#track-content').addClass('hidden');
    $('#track-result-section').removeClass('hidden');
    $('#track-error-title').text('Pesanan tidak ditemukan');
    $('#track-error-desc').text(msg || 'Periksa kembali kode pesanan atau nama pemesan Anda.');
    $('#track-error').removeClass('hidden');
  }

  /* Inisialisasi halaman */
  init();
});
