/**
 * Script Halaman Riwayat Transaksi Kasir/Admin.
 * FR-HIS-01..03, prd.md §6.13, §12.3, §12.4.
 *
 * Fitur:
 * 1. Filter tanggal (default: hari ini), metode, status, pencarian.
 * 2. Tabel baris riwayat pembayaran + baris total footer untuk status paid.
 * 3. Pagination server-side.
 * 4. Klik baris → modal detail + tombol Cetak Ulang Struk.
 * 5. Admin: tombol Void + modal konfirmasi alasan.
 */
$(function () {
  'use strict';

  /* ------------------------------------------------------------------ */
  /* Konfigurasi & state                                                  */
  /* ------------------------------------------------------------------ */
  const cfg      = window.HistoryConfig || { isAdmin: false, receiptBase: '/foodease/public/cashier/receipt.php?code=' };
  const API_LIST = 'api/payments/list.php';
  const API_VOID = 'api/payments/void.php';

  let state = {
    page:    1,
    pages:   1,
    filters: {},
    currentPayment: null,   // data baris yang sedang dibuka di modal
  };

  /* ------------------------------------------------------------------ */
  /* Inisialisasi: set tanggal default = hari ini                        */
  /* ------------------------------------------------------------------ */
  function init() {
    const today = new Date().toISOString().slice(0, 10);
    $('#filter-date-from').val(today);
    $('#filter-date-to').val(today);

    bindEvents();
    loadHistory();
  }

  /* ------------------------------------------------------------------ */
  /* Event binding                                                        */
  /* ------------------------------------------------------------------ */
  function bindEvents() {
    // Terapkan filter
    $('#btn-filter').on('click', function () {
      state.page = 1;
      loadHistory();
    });

    // Tekan Enter di kolom cari
    $('#filter-q').on('keydown', function (e) {
      if (e.key === 'Enter') {
        state.page = 1;
        loadHistory();
      }
    });

    // Reset filter
    $('#btn-reset').on('click', function () {
      const today = new Date().toISOString().slice(0, 10);
      $('#filter-date-from').val(today);
      $('#filter-date-to').val(today);
      $('#filter-method').val('');
      $('#filter-status').val('');
      $('#filter-q').val('');
      state.page = 1;
      loadHistory();
    });

    // Pagination
    $('#btn-prev').on('click', function () {
      if (state.page > 1) {
        state.page--;
        loadHistory();
      }
    });

    $('#btn-next').on('click', function () {
      if (state.page < state.pages) {
        state.page++;
        loadHistory();
      }
    });

    // Retry setelah error
    $('#btn-retry').on('click', loadHistory);

    // Tutup modal detail
    $('#btn-close-detail').on('click', closeDetailModal);
    $('#detail-modal').on('click', function (e) {
      if (e.target === this) closeDetailModal();
    });

    // Tombol Void di footer modal detail (admin)
    $('#btn-void').on('click', function () {
      closeDetailModal();
      openVoidModal();
    });

    // Modal Void
    if (cfg.isAdmin) {
      $('#btn-close-void, #btn-cancel-void').on('click', closeVoidModal);
      $('#void-modal').on('click', function (e) {
        if (e.target === this) closeVoidModal();
      });
      $('#btn-confirm-void').on('click', confirmVoid);
    }
  }

  /* ------------------------------------------------------------------ */
  /* Ambil data dari API                                                  */
  /* ------------------------------------------------------------------ */
  function loadHistory() {
    showLoading(true);

    const params = {
      page:      state.page,
      per_page:  20,
      date_from: $('#filter-date-from').val(),
      date_to:   $('#filter-date-to').val(),
      method:    $('#filter-method').val(),
      status:    $('#filter-status').val(),
      q:         $('#filter-q').val(),
    };

    state.filters = params;

    Api.get(API_LIST, params)
      .then(function (res) {
        showLoading(false);

        const payments   = (res.data && res.data.payments)   ? res.data.payments   : [];
        const pagination = (res.data && res.data.pagination) ? res.data.pagination : {};
        const paidTotal  = (res.data && res.data.paid_total) ? res.data.paid_total : 0;

        state.pages = pagination.pages || 1;

        if (!payments.length) {
          showEmpty(true);
          return;
        }

        renderTable(payments, paidTotal);
        renderPagination(pagination);
      })
      .catch(function (err) {
        showLoading(false);
        showError(err.message || 'Gagal memuat riwayat transaksi.');
      });
  }

  /* ------------------------------------------------------------------ */
  /* Render tabel                                                         */
  /* ------------------------------------------------------------------ */
  function renderTable(payments, paidTotal) {
    const $tbody = $('#history-tbody').empty();

    payments.forEach(function (p) {
      const isPaid = p.status === 'paid';
      const methodLabel = p.method === 'cash' ? 'Tunai' : 'QRIS';

      const $tr = $('<tr>').addClass(
        'border-b border-line hover:bg-surface cursor-pointer transition-colors'
      );

      // Kode Bayar
      $('<td>').addClass('h-12 px-4 font-mono text-xs tabular-nums text-ink')
        .text(p.payment_code)
        .appendTo($tr);

      // Kode Pesanan
      $('<td>').addClass('h-12 px-4 font-mono text-xs tabular-nums text-subtle')
        .text(p.order_code)
        .appendTo($tr);

      // Waktu
      $('<td>').addClass('h-12 px-4 text-xs text-subtle whitespace-nowrap')
        .text(window.Fmt.datetime(p.paid_at))
        .appendTo($tr);

      // Kasir
      $('<td>').addClass('h-12 px-4 text-xs text-ink')
        .text(p.cashier_name || '-')
        .appendTo($tr);

      // Metode
      $('<td>').addClass('h-12 px-4 text-xs text-ink')
        .text(methodLabel)
        .appendTo($tr);

      // Total
      $('<td>').addClass('h-12 px-4 text-right tabular-nums text-sm font-medium text-ink')
        .text(window.Fmt.rupiah(p.total))
        .appendTo($tr);

      // Status badge
      $('<td>').addClass('h-12 px-4 text-center')
        .html(window.Fmt.statusBadge(p.status))
        .appendTo($tr);

      // Aksi (admin: tombol Void)
      if (cfg.isAdmin) {
        const $td = $('<td>').addClass('h-12 px-4 text-center');
        if (isPaid) {
          $('<button>')
            .addClass(
              'h-7 px-3 rounded-full bg-danger-light text-danger text-xs font-medium hover:bg-red-100 transition-colors btn-void-row'
            )
            .text('Void')
            .on('click', function (e) {
              e.stopPropagation();
              state.currentPayment = p;
              openVoidModal();
            })
            .appendTo($td);
        }
        $td.appendTo($tr);
      }

      // Klik baris → buka detail
      $tr.on('click', function () {
        state.currentPayment = p;
        openDetailModal(p);
      });

      $tbody.append($tr);
    });

    // Footer total paid
    if (paidTotal > 0 || payments.some(function (p) { return p.status === 'paid'; })) {
      $('#history-foot-total').text(window.Fmt.rupiah(paidTotal));
      $('#history-tfoot').removeClass('hidden');
    } else {
      $('#history-tfoot').addClass('hidden');
    }

    $('#history-table-wrap').removeClass('hidden');
    $('#history-empty').addClass('hidden');
  }

  /* ------------------------------------------------------------------ */
  /* Pagination                                                           */
  /* ------------------------------------------------------------------ */
  function renderPagination(pagination) {
    const total   = pagination.total   || 0;
    const page    = pagination.page    || 1;
    const perPage = pagination.per_page|| 20;
    const pages   = pagination.pages   || 1;

    const from = total ? (page - 1) * perPage + 1 : 0;
    const to   = Math.min(page * perPage, total);

    $('#pagination-info').text(
      total
        ? 'Menampilkan ' + from + '–' + to + ' dari ' + total + ' transaksi'
        : 'Tidak ada transaksi'
    );

    $('#btn-prev').prop('disabled', page <= 1);
    $('#btn-next').prop('disabled', page >= pages);

    $('#history-pagination').removeClass('hidden');
  }

  /* ------------------------------------------------------------------ */
  /* Modal Detail                                                         */
  /* ------------------------------------------------------------------ */
  function openDetailModal(p) {
    const methodLabel = p.method === 'cash' ? 'Tunai' : 'QRIS';
    const isPaid      = p.status === 'paid';

    let typeText = p.order_type === 'dine_in' ? 'Makan di Tempat' : 'Bawa Pulang';
    if (p.order_type === 'dine_in' && p.table_number) {
      typeText += ' — Meja ' + p.table_number;
    }

    let html = '<dl class="grid grid-cols-2 gap-x-4 gap-y-3">';

    function row(label, value) {
      html += '<dt class="text-xs text-subtle font-medium">' + escHtml(label) + '</dt>';
      html += '<dd class="text-xs text-ink font-medium text-right">' + value + '</dd>';
    }

    row('Kode Bayar',   '<span class="font-mono tabular-nums">' + escHtml(p.payment_code) + '</span>');
    row('Kode Pesanan', '<span class="font-mono tabular-nums">' + escHtml(p.order_code)   + '</span>');
    row('Pelanggan',    escHtml(p.customer_name || '-'));
    row('Tipe',         escHtml(typeText));
    row('Waktu',        escHtml(window.Fmt.datetime(p.paid_at)));
    row('Kasir',        escHtml(p.cashier_name || '-'));
    row('Metode',       escHtml(methodLabel));
    row('Total',        '<span class="tabular-nums">' + window.Fmt.rupiah(p.total) + '</span>');

    if (p.method === 'cash') {
      row('Dibayar',    '<span class="tabular-nums">' + window.Fmt.rupiah(p.amount_paid)   + '</span>');
      row('Kembalian',  '<span class="tabular-nums">' + window.Fmt.rupiah(p.change_amount) + '</span>');
    }

    row('Status', window.Fmt.statusBadge(p.status));

    if (p.status === 'void') {
      row('Void oleh',  escHtml(p.voided_by_name || '-'));
      row('Alasan',     escHtml(p.void_reason    || '-'));
    }

    html += '</dl>';

    $('#detail-modal-body').html(html);

    // Link cetak ulang struk
    $('#btn-reprint').attr('href', cfg.receiptBase + encodeURIComponent(p.payment_code));

    // Tombol Void hanya tampil jika paid dan admin
    if (cfg.isAdmin) {
      $('#btn-void').toggleClass('hidden', !isPaid);
    }

    $('#detail-modal').removeClass('hidden').addClass('flex');
  }

  function closeDetailModal() {
    $('#detail-modal').addClass('hidden').removeClass('flex');
    state.currentPayment = null;
  }

  /* ------------------------------------------------------------------ */
  /* Modal Void (Admin)                                                   */
  /* ------------------------------------------------------------------ */
  function openVoidModal() {
    $('#void-reason').val('');
    $('#void-reason-error').addClass('hidden').text('');
    $('#void-modal').removeClass('hidden').addClass('flex');
    setTimeout(function () { $('#void-reason').trigger('focus'); }, 50);
  }

  function closeVoidModal() {
    $('#void-modal').addClass('hidden').removeClass('flex');
  }

  function confirmVoid() {
    const reason = $('#void-reason').val().trim();
    if (reason.length < 5) {
      $('#void-reason-error').text('Alasan minimal 5 karakter.').removeClass('hidden');
      return;
    }
    $('#void-reason-error').addClass('hidden');

    const p = state.currentPayment;
    if (!p) { return; }

    $('#btn-confirm-void').prop('disabled', true).text('Memproses...');

    Api.post(API_VOID, { order_id: p.order_id, reason: reason })
      .then(function (res) {
        closeVoidModal();
        window.UI && window.UI.toast(res.message || 'Void berhasil.');
        loadHistory();
      })
      .catch(function (err) {
        $('#void-reason-error').text(err.message || 'Gagal melakukan void.').removeClass('hidden');
      })
      .finally(function () {
        $('#btn-confirm-void').prop('disabled', false).text('Void');
      });
  }

  /* ------------------------------------------------------------------ */
  /* State helpers                                                        */
  /* ------------------------------------------------------------------ */
  function showLoading(show) {
    $('#history-loading').toggleClass('hidden', !show);
    if (show) {
      $('#history-table-wrap, #history-empty, #history-error, #history-pagination').addClass('hidden');
    }
  }

  function showEmpty(show) {
    $('#history-empty').toggleClass('hidden', !show);
    if (show) {
      $('#history-table-wrap, #history-tfoot, #history-pagination').addClass('hidden');
    }
  }

  function showError(msg) {
    $('#history-error-text').text(msg);
    $('#history-error').removeClass('hidden');
    $('#history-table-wrap, #history-empty').addClass('hidden');
  }

  /* ------------------------------------------------------------------ */
  /* Util: escape HTML (tanpa .html() mentah untuk data pengguna)        */
  /* ------------------------------------------------------------------ */
  function escHtml(str) {
    return $('<div>').text(String(str || '')).html();
  }

  /* ------------------------------------------------------------------ */
  /* Boot                                                                 */
  /* ------------------------------------------------------------------ */
  init();
});
