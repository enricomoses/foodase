/**
 * Script Halaman Laporan Penjualan Admin (FR-RPT-01..07).
 * Gaya Uber Eats: Bersih, dominan hitam-putih, tanpa chart library eksternal.
 */
$(function () {
  'use strict';

  const cfg = window.ReportConfig || {
    apiSummary:     '/foodease/public/api/reports/summary.php',
    apiBestSellers: '/foodease/public/api/reports/best-sellers.php',
    apiDaily:       '/foodease/public/api/reports/daily.php',
    exportUrl:      '/foodease/public/api/reports/export.php'
  };

  /**
   * Helper format tanggal ke string YYYY-MM-DD
   */
  function formatDate(d) {
    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  /**
   * Helper format Rupiah aman
   */
  function fmtRupiah(amount) {
    if (window.Fmt && typeof window.Fmt.rupiah === 'function') {
      return window.Fmt.rupiah(amount);
    }
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
  }

  /**
   * Helper format tanggal tampilan Indonesia (DD MMM YYYY)
   */
  function fmtDisplayDate(dateStr) {
    if (!dateStr) return '-';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    const mIdx = parseInt(parts[1], 10) - 1;
    return `${parseInt(parts[2], 10)} ${months[mIdx] || parts[1]} ${parts[0]}`;
  }

  /**
   * Escape HTML string
   */
  function escHtml(str) {
    return $('<div>').text(String(str || '')).html();
  }

  /**
   * Inisialisasi rentang tanggal default (awal bulan s.d. hari ini)
   */
  function initDates() {
    const now = new Date();
    const today = formatDate(now);

    const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const startMonth = formatDate(firstOfMonth);

    $('#report-from').val(startMonth);
    $('#report-to').val(today);
  }

  /**
   * Event bindings
   */
  function bindEvents() {
    // Tombol Cepat Periode
    $('.btn-quick-date').on('click', function () {
      const range = $(this).data('range');
      const now = new Date();
      const today = formatDate(now);

      $('.btn-quick-date').removeClass('bg-black text-white').addClass('bg-surface text-ink');
      $(this).removeClass('bg-surface text-ink').addClass('bg-black text-white');

      if (range === 'today') {
        $('#report-from').val(today);
        $('#report-to').val(today);
      } else if (range === '7days') {
        const past7 = new Date();
        past7.setDate(now.getDate() - 6);
        $('#report-from').val(formatDate(past7));
        $('#report-to').val(today);
      } else if (range === 'month') {
        const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        $('#report-from').val(formatDate(firstOfMonth));
        $('#report-to').val(today);
      }

      loadAllReports();
    });

    // Tombol Terapkan
    $('#btn-apply-report').on('click', function () {
      $('.btn-quick-date').removeClass('bg-black text-white').addClass('bg-surface text-ink');
      loadAllReports();
    });

    // Tekan Enter pada input tanggal
    $('#report-from, #report-to').on('keydown', function (e) {
      if (e.key === 'Enter') {
        loadAllReports();
      }
    });

    // Coba Lagi saat error
    $('#btn-report-retry').on('click', function () {
      loadAllReports();
    });

    // Ekspor CSV
    $('#btn-export-csv').on('click', function () {
      const from = $('#report-from').val().trim();
      const to   = $('#report-to').val().trim();
      if (!from || !to) {
        alert('Silakan pilih tanggal awal dan akhir terlebih dahulu.');
        return;
      }
      const exportUrl = cfg.exportUrl + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
      window.location.href = exportUrl;
    });
  }

  /**
   * Muat seluruh data laporan
   */
  function loadAllReports() {
    const from = $('#report-from').val().trim();
    const to   = $('#report-to').val().trim();

    if (!from || !to) {
      showError('Tanggal awal dan akhir harus diisi.');
      return;
    }

    if (from > to) {
      showError('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
      return;
    }

    hideError();
    setLoadingState(true);

    const params = { from: from, to: to };

    // 1. Muat Summary & Breakdown Metode
    const p1 = Api.get(cfg.apiSummary, params)
      .then(function (res) {
        renderSummary(res.data?.summary || {});
        renderMethods(res.data?.by_method || [], res.data?.summary?.total_revenue || 0);
      });

    // 2. Muat 10 Menu Terlaris
    const p2 = Api.get(cfg.apiBestSellers, Object.assign({}, params, { limit: 10 }))
      .then(function (res) {
        renderBestSellers(res.data?.items || []);
      });

    // 3. Muat Pendapatan Harian
    const p3 = Api.get(cfg.apiDaily, params)
      .then(function (res) {
        renderDaily(res.data?.daily || []);
      });

    Promise.all([p1, p2, p3])
      .catch(function (err) {
        showError(err.message || 'Gagal memuat laporan penjualan.');
      })
      .finally(function () {
        setLoadingState(false);
      });
  }

  /**
   * Render kartu ringkasan
   */
  function renderSummary(summary) {
    const rev   = summary.total_revenue || 0;
    const count = summary.transaction_count || 0;
    const avg   = summary.average_per_transaction || 0;

    $('#card-revenue').text(fmtRupiah(rev));
    $('#card-count').text(Number(count).toLocaleString('id-ID') + ' transaksi');
    $('#card-average').text(fmtRupiah(avg));
  }

  /**
   * Render menu terlaris (dengan bar horizontal Tailwind sederhana)
   */
  function renderBestSellers(items) {
    const $tbody = $('#bestsellers-tbody').empty();

    if (!items || items.length === 0) {
      $('#bestsellers-empty').removeClass('hidden');
      $('#bestsellers-content').addClass('hidden');
      return;
    }

    $('#bestsellers-empty').addClass('hidden');
    $('#bestsellers-content').removeClass('hidden');

    const maxQty = Math.max(...items.map(function (it) { return it.total_qty; }), 1);

    items.forEach(function (item, index) {
      const percent = Math.min(Math.round((item.total_qty / maxQty) * 100), 100);

      const $tr = $('<tr>').addClass('hover:bg-surface transition-colors');

      // Rank
      $('<td>')
        .addClass('py-3 text-xs font-mono font-bold text-subtle')
        .text(index + 1)
        .appendTo($tr);

      // Nama Menu + Bar visualisasi
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

      // Pendapatan
      $('<td>')
        .addClass('py-3 text-right text-xs font-bold text-ink tabular-nums')
        .text(fmtRupiah(item.total_revenue))
        .appendTo($tr);

      $tbody.append($tr);
    });
  }

  /**
   * Render breakdown pendapatan per metode pembayaran
   */
  function renderMethods(methods, totalRevenue) {
    const $container = $('#methods-content').empty();

    if (!methods || methods.length === 0) {
      $('#methods-empty').removeClass('hidden');
      $('#methods-content').addClass('hidden');
      return;
    }

    $('#methods-empty').addClass('hidden');
    $('#methods-content').removeClass('hidden');

    methods.forEach(function (m) {
      const label = (m.method === 'cash') ? 'Tunai (Cash)' : 'QRIS';
      const percent = totalRevenue > 0 ? Math.round((m.total_revenue / totalRevenue) * 100) : 0;

      const $card = $('<div>').addClass('p-4 rounded-xl border border-line bg-surface/50 space-y-2');

      const $top = $('<div>').addClass('flex items-center justify-between');
      $('<span>').addClass('text-xs font-bold text-ink').text(label).appendTo($top);
      $('<span>').addClass('text-xs font-bold text-ink tabular-nums').text(fmtRupiah(m.total_revenue)).appendTo($top);
      $top.appendTo($card);

      // Progress bar proporsi
      const $barWrap = $('<div>').addClass('h-2 w-full bg-line rounded-full overflow-hidden');
      $('<div>')
        .addClass('h-full bg-black rounded-full')
        .css('width', percent + '%')
        .appendTo($barWrap);
      $barWrap.appendTo($card);

      const $bot = $('<div>').addClass('flex items-center justify-between text-[11px] text-subtle');
      $('<span>').text(Number(m.transaction_count).toLocaleString('id-ID') + ' transaksi').appendTo($bot);
      $('<span>').text(percent + '% dari total').appendTo($bot);
      $bot.appendTo($card);

      $container.append($card);
    });
  }

  /**
   * Render tabel pendapatan harian
   */
  function renderDaily(dailyList) {
    const $tbody = $('#daily-tbody').empty();

    if (!dailyList || dailyList.length === 0) {
      $('#daily-empty').removeClass('hidden');
      $('#daily-table-wrap').addClass('hidden');
      return;
    }

    $('#daily-empty').addClass('hidden');
    $('#daily-table-wrap').removeClass('hidden');

    let sumCount = 0;
    let sumRev   = 0;

    dailyList.forEach(function (row) {
      sumCount += row.transaction_count;
      sumRev   += row.total_revenue;

      const $tr = $('<tr>').addClass('border-b border-line hover:bg-surface transition-colors');

      $('<td>')
        .addClass('h-11 px-4 text-xs font-medium text-ink')
        .text(fmtDisplayDate(row.date))
        .appendTo($tr);

      $('<td>')
        .addClass('h-11 px-4 text-right text-xs tabular-nums text-subtle')
        .text(Number(row.transaction_count).toLocaleString('id-ID') + ' transaksi')
        .appendTo($tr);

      $('<td>')
        .addClass('h-11 px-4 text-right text-xs font-bold tabular-nums text-ink')
        .text(fmtRupiah(row.total_revenue))
        .appendTo($tr);

      $tbody.append($tr);
    });

    $('#daily-foot-count').text(Number(sumCount).toLocaleString('id-ID') + ' transaksi');
    $('#daily-foot-total').text(fmtRupiah(sumRev));
  }

  /**
   * Helper pengaturan state loading
   */
  function setLoadingState(isLoading) {
    if (isLoading) {
      $('#bestsellers-loading, #methods-loading, #daily-loading').removeClass('hidden');
      $('#bestsellers-content, #bestsellers-empty, #methods-content, #methods-empty, #daily-table-wrap, #daily-empty').addClass('hidden');
    } else {
      $('#bestsellers-loading, #methods-loading, #daily-loading').addClass('hidden');
    }
  }

  /**
   * Tampilkan pesan error
   */
  function showError(msg) {
    $('#report-error-text').text(msg);
    $('#report-error').removeClass('hidden');
  }

  /**
   * Sembunyikan error
   */
  function hideError() {
    $('#report-error').addClass('hidden');
  }

  // Inisialisasi
  initDates();
  bindEvents();
  loadAllReports();
});
