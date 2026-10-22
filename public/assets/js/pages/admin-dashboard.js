/**
 * Script Halaman Dashboard Admin (FR-RPT-06).
 * Memakai endpoint yang sama dengan laporan (api/reports/summary.php & best-sellers.php).
 */
$(function () {
  'use strict';

  const cfg = window.DashboardConfig || {
    apiSummary:     '/foodease/public/api/reports/summary.php',
    apiBestSellers: '/foodease/public/api/reports/best-sellers.php'
  };

  /**
   * Format Date YYYY-MM-DD
   */
  function formatDate(d) {
    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  /**
   * Format Rupiah aman
   */
  function fmtRupiah(amount) {
    if (window.Fmt && typeof window.Fmt.rupiah === 'function') {
      return window.Fmt.rupiah(amount);
    }
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
  }

  /**
   * Format Tanggal Panjang Bahasa Indonesia
   */
  function formatLongDate(d) {
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  }

  /**
   * Escape HTML
   */
  function escHtml(str) {
    return $('<div>').text(String(str || '')).html();
  }

  /**
   * Muat data dashboard hari ini
   */
  function loadDashboardData() {
    const now = new Date();
    const today = formatDate(now);

    $('#dash-date-text').text(formatLongDate(now));
    $('#dash-error').addClass('hidden');
    setLoadingState(true);

    const params = { from: today, to: today };

    // 1. Ringkasan performa hari ini & jumlah pesanan
    const pSummary = Api.get(cfg.apiSummary, params)
      .then(function (res) {
        const summary     = res.data?.summary || {};
        const orderCounts = res.data?.order_counts || {};

        const rev   = summary.total_revenue || 0;
        const count = summary.transaction_count || 0;
        const unpaid = orderCounts.unpaid_orders || 0;
        const proc   = orderCounts.processing_orders || 0;

        $('#dash-today-revenue').text(fmtRupiah(rev));
        $('#dash-today-count').text(Number(count).toLocaleString('id-ID') + ' transaksi');
        $('#dash-today-unpaid').text(Number(unpaid).toLocaleString('id-ID') + ' pesanan');
        $('#dash-today-processing').text(Number(proc).toLocaleString('id-ID') + ' pesanan');
      });

    // 2. 5 Menu terlaris hari ini
    const pBest = Api.get(cfg.apiBestSellers, Object.assign({}, params, { limit: 5 }))
      .then(function (res) {
        renderTop5(res.data?.items || []);
      });

    Promise.all([pSummary, pBest])
      .catch(function (err) {
        $('#dash-error-text').text(err.message || 'Gagal memuat data dashboard.');
        $('#dash-error').removeClass('hidden');
      })
      .finally(function () {
        setLoadingState(false);
      });
  }

  /**
   * Render 5 menu terlaris hari ini
   */
  function renderTop5(items) {
    const $tbody = $('#dash-bestsellers-tbody').empty();

    if (!items || items.length === 0) {
      $('#dash-bestsellers-empty').removeClass('hidden');
      $('#dash-bestsellers-content').addClass('hidden');
      return;
    }

    $('#dash-bestsellers-empty').addClass('hidden');
    $('#dash-bestsellers-content').removeClass('hidden');

    const maxQty = Math.max(...items.map(function (it) { return it.total_qty; }), 1);

    items.forEach(function (item, index) {
      const percent = Math.min(Math.round((item.total_qty / maxQty) * 100), 100);

      const $tr = $('<tr>').addClass('hover:bg-surface transition-colors');

      // Rank
      $('<td>')
        .addClass('py-3 text-xs font-mono font-bold text-subtle')
        .text(index + 1)
        .appendTo($tr);

      // Menu name + horizontal bar
      const $tdMenu = $('<td>').addClass('py-3 pr-4');
      $('<div>')
        .addClass('text-xs font-medium text-ink')
        .text(item.menu_name)
        .appendTo($tdMenu);

      const $barWrap = $('<div>').addClass('h-1.5 w-full bg-surface rounded-full overflow-hidden mt-1.5');
      $('<div>')
        .addClass('h-full bg-black rounded-full transition-all duration-300')
        .css('width', percent + '%')
        .appendTo($barWrap);
      $barWrap.appendTo($tdMenu);
      $tdMenu.appendTo($tr);

      // Terjual
      $('<td>')
        .addClass('py-3 text-right text-xs font-medium text-ink tabular-nums')
        .text(Number(item.total_qty).toLocaleString('id-ID') + ' porsi')
        .appendTo($tr);

      // Total Pendapatan
      $('<td>')
        .addClass('py-3 text-right text-xs font-bold text-ink tabular-nums')
        .text(fmtRupiah(item.total_revenue))
        .appendTo($tr);

      $tbody.append($tr);
    });
  }

  /**
   * Loading state helper
   */
  function setLoadingState(isLoading) {
    if (isLoading) {
      $('#dash-bestsellers-loading').removeClass('hidden');
      $('#dash-bestsellers-content, #dash-bestsellers-empty').addClass('hidden');
    } else {
      $('#dash-bestsellers-loading').addClass('hidden');
    }
  }

  // Event retry
  $('#btn-dash-retry').on('click', loadDashboardData);

  // Inisialisasi
  loadDashboardData();
});
