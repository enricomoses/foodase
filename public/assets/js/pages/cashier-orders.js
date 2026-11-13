/**
 * Script Halaman Pesanan Masuk Kasir — POS Terminal Workspace.
 * FR-COR-01..04, FR-PAY-01..04, prd.md §6.9, §6.11, §12.3, §12.4.
 *
 * Fitur:
 * 1. Dual-Pane POS Terminal: Daftar antrean di kiri + Terminal rincian & pembayaran langsung di kanan.
 * 2. Tab status (Belum dibayar, Diproses, Siap, Selesai, Batal) + counter badge per tab.
 * 3. Pencarian kode/nama pemesan (debounce 300 ms), filter tipe (dine-in/takeaway), filter tanggal.
 * 4. Pembayaran langsung tanpa modal beruntun (Tunai dengan tombol nominal cepat & live kembalian, QRIS).
 * 5. Update status instan (1-klik Tandai Siap, 1-klik Tandai Selesai) untuk melayani pelanggan cepat.
 * 6. Auto-refresh tiap 8 detik (berhenti saat tab background / document.hidden).
 * 7. Akses cetak struk termal instan (cashier/receipt.php).
 */
$(function () {
  'use strict';

  /* ============================================================
     HELPERS & CONSTANTS
     ============================================================ */
  const PLACEHOLDER = 'assets/img/placeholder-menu.svg';

  function getBaseUrl() {
    const baseEl = document.querySelector('base');
    return baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function timeAgo(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr.replace(/-/g, '/'));
    const now = new Date();
    const diffSec = Math.floor((now.getTime() - d.getTime()) / 1000);

    if (diffSec < 60) return 'Baru saja';
    const diffMin = Math.floor(diffSec / 60);
    if (diffMin < 60) return diffMin + ' mnt lalu';
    const diffHours = Math.floor(diffMin / 60);
    if (diffHours < 24) return diffHours + ' jam lalu';
    return window.Fmt.datetime(dateStr);
  }

  /* ============================================================
     STATE
     ============================================================ */
  let currentStatus          = 'pending';
  let currentPage             = 1;
  let totalPages              = 1;
  let searchTimer             = null;
  let autoRefreshTimer        = null;
  let isRequestPending        = false;
  let knownOrderCodes         = new Set();
  let isInitialLoad           = true;
  let selectedOrderCode       = null;
  let currentSelectedOrder    = null;
  let pendingCancelOrder      = null;
  let currentPaymentMethod    = 'cash';
  const userRole              = $('#cashier-orders-page').data('user-role') || 'cashier';

  /* ============================================================
     INISIALISASI
     ============================================================ */
  function init() {
    bindEvents();
    fetchOrders(false);
    startAutoRefresh();
    setupVisibilityWatcher();
  }

  /* ============================================================
     EVENT LISTENERS
     ============================================================ */
  function bindEvents() {
    // 1. Tab Status
    $('#status-tabs').on('click', '.status-tab', function () {
      const status = $(this).data('status');
      if (status === currentStatus) return;

      currentStatus = status;
      currentPage = 1;
      selectedOrderCode = null;
      closeTerminalPanel();

      // Update tampilan tab
      $('.status-tab').each(function () {
        const isActive = $(this).data('status') === status;
        const $badge = $(this).find('span:last-child');

        $(this)
          .toggleClass('bg-black text-white font-bold', isActive)
          .toggleClass('bg-surface text-subtle font-medium', !isActive);

        $badge
          .toggleClass('bg-white/20 text-white font-bold', isActive)
          .toggleClass('bg-line text-ink font-medium', !isActive);
      });

      fetchOrders(false);
    });

    // 2. Pencarian (Debounce 300ms)
    $('#input-search').on('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () {
        currentPage = 1;
        fetchOrders(false);
      }, 300);
    });

    // 3. Filter Tipe
    $('#select-type').on('change', function () {
      currentPage = 1;
      fetchOrders(false);
    });

    // 4. Filter Tanggal
    $('#input-date').on('change', function () {
      currentPage = 1;
      fetchOrders(false);
    });

    // 5. Tombol Bersihkan Tanggal (Semua)
    $('#btn-clear-date').on('click', function () {
      $('#input-date').val('');
      currentPage = 1;
      fetchOrders(false);
    });

    // 6. Tombol Segarkan Manual
    $('#btn-refresh-orders').on('click', function () {
      fetchOrders(false);
    });

    // 7. Tombol Notifikasi Pesanan Baru
    $('#btn-new-orders-alert').on('click', function () {
      $(this).addClass('hidden').removeClass('inline-flex');
      fetchOrders(false);
    });

    // 8. Paginasi
    $('#btn-prev-page').on('click', function () {
      if (currentPage > 1) {
        currentPage--;
        fetchOrders(false);
      }
    });

    $('#btn-next-page').on('click', function () {
      if (currentPage < totalPages) {
        currentPage++;
        fetchOrders(false);
      }
    });

    // 9. Retry Button
    $('#btn-retry-orders').on('click', function () {
      fetchOrders(false);
    });

    // 10. Klik Baris Tabel → Buka Rincian di Panel Kanan
    $('#orders-table-body').on('click', 'tr', function (e) {
      if ($(e.target).closest('.quick-action-btn').length) return;
      const code = $(this).attr('data-order-code');
      if (code) {
        selectOrder(code, false);
      }
    });

    // 10b. Tombol Aksi Cepat di Baris: Bayar (Fokuskan input pembayaran langsung)
    $('#orders-table-body').on('click', '.qa-pay', function (e) {
      e.stopPropagation();
      const code = $(this).closest('tr').attr('data-order-code');
      if (code) {
        selectOrder(code, true);
      }
    });

    // 10c. Tombol Aksi Cepat: Siap (Diproses → Siap Saji)
    $('#orders-table-body').on('click', '.qa-ready', function (e) {
      e.stopPropagation();
      const $btn = $(this);
      const orderData = $btn.closest('tr').data('order-obj');
      if (!orderData) return;
      quickUpdateStatus(orderData, 'ready', $btn);
    });

    // 10d. Tombol Aksi Cepat: Selesai (Siap → Selesai)
    $('#orders-table-body').on('click', '.qa-complete', function (e) {
      e.stopPropagation();
      const $btn = $(this);
      const orderData = $btn.closest('tr').data('order-obj');
      if (!orderData) return;
      quickUpdateStatus(orderData, 'completed', $btn);
    });

    // 11. Tutup Terminal Panel (Mobile Drawer / Deselect)
    $('#btn-terminal-close, #pos-terminal-backdrop').on('click', function () {
      closeTerminalPanel();
    });

    // 12. Segmented Control Metode Pembayaran (Tunai vs QRIS)
    $('#terminal-method-cash').on('click', function () {
      setPaymentMethod('cash');
    });

    $('#terminal-method-qris').on('click', function () {
      setPaymentMethod('qris');
    });

    // 13. Input Nominal Tunai (Format Ribuan & Live Kembalian)
    $('#terminal-cash-input').on('input', function () {
      const raw = $(this).val().replace(/\D/g, '');
      const num = parseInt(raw, 10);
      if (!isNaN(num) && num > 0) {
        $(this).val(num.toLocaleString('id-ID'));
      } else {
        $(this).val('');
      }
      updateChangeCalculation();
    });

    // Submit tunai lewat tombol Enter di input
    $('#terminal-cash-input').on('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        $('#btn-terminal-submit-cash').trigger('click');
      }
    });

    // 14. Klik Tombol Nominal Cepat
    $('#terminal-quick-amounts').on('click', '.terminal-quick-pill', function () {
      const amount = $(this).data('amount');
      const num = parseInt(amount, 10) || 0;
      $('#terminal-cash-input').val(num.toLocaleString('id-ID'));
      updateChangeCalculation();
      $('#terminal-cash-input').focus();
    });

    // 15. Eksekusi Pembayaran Tunai
    $('#btn-terminal-submit-cash').on('click', function () {
      processCashPayment();
    });

    // 16. Eksekusi Pembayaran QRIS
    $('#btn-terminal-submit-qris').on('click', function () {
      processQrisPayment();
    });

    // 17. Update Status dari Panel Terminal
    $('#btn-terminal-mark-ready').on('click', function () {
      if (!currentSelectedOrder) return;
      quickUpdateStatus(currentSelectedOrder, 'ready', $(this));
    });

    $('#btn-terminal-mark-completed').on('click', function () {
      if (!currentSelectedOrder) return;
      quickUpdateStatus(currentSelectedOrder, 'completed', $(this));
    });

    // 18. Tombol Batalkan Pesanan dari Terminal
    $('#btn-terminal-open-cancel, #btn-terminal-cancel-admin-proc').on('click', function () {
      if (currentSelectedOrder) {
        openCancelModal(currentSelectedOrder);
      }
    });

    // 19. Konfirmasi Submit Pembatalan
    $('#btn-confirm-cancel-submit').on('click', function () {
      handleCancelSubmit($(this));
    });

    // 20. Dismiss Modal Cancel
    $(document).on('click', '[data-modal-dismiss]', function () {
      $('#modal-cancel-order').addClass('hidden');
    });

    // 21. Keyboard ESC untuk tutup drawer di mobile
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') {
        if (!$('#modal-cancel-order').hasClass('hidden')) {
          $('#modal-cancel-order').addClass('hidden');
        } else if ($('#pos-terminal-panel').hasClass('mobile-open')) {
          closeTerminalPanel();
        }
      }
    });
  }

  /* ============================================================
     FETCH ORDERS (AJAX)
     ============================================================ */
  function fetchOrders(isBackground) {
    if (isRequestPending) return;
    isRequestPending = true;

    if (!isBackground) {
      showLoading(true);
      $('#table-error').addClass('hidden');
    }

    const params = {
      status:   currentStatus,
      q:        $('#input-search').val().trim(),
      type:     $('#select-type').val(),
      date:     $('#input-date').val(),
      page:     currentPage,
      per_page: 20
    };

    $.ajax({
      url:      getBaseUrl() + 'api/orders/list.php',
      type:     'GET',
      data:     params,
      dataType: 'json'
    })
    .done(function (res) {
      isRequestPending = false;
      showLoading(false);

      if (res && res.success && res.data) {
        handleOrdersSuccess(res.data, isBackground);
      } else {
        showError(res.message || 'Gagal memuat pesanan.');
      }
    })
    .fail(function (jqXHR) {
      isRequestPending = false;
      showLoading(false);
      if (!isBackground) {
        const res = jqXHR.responseJSON;
        showError((res && res.message) ? res.message : 'Terjadi kesalahan saat memuat data.');
      }
    });
  }

  /* ============================================================
     SUKSES MEMUAT DATA
     ============================================================ */
  function handleOrdersSuccess(data, isBackground) {
    const orders     = data.orders || [];
    const pagination = data.pagination || {};
    const tabCounts  = data.tab_counts || {};

    // 1. Update jumlah counter di tab
    updateTabCounts(tabCounts);

    // 2. Deteksi pesanan baru saat background refresh
    if (isBackground && !isInitialLoad) {
      let newCount = 0;
      orders.forEach(function (o) {
        if (!knownOrderCodes.has(o.order_code)) {
          newCount++;
        }
      });

      if (newCount > 0) {
        $('#new-orders-text').text(newCount + ' pesanan baru');
        $('#btn-new-orders-alert').removeClass('hidden').addClass('inline-flex');
      }
    }

    orders.forEach(function (o) {
      knownOrderCodes.add(o.order_code);
    });
    isInitialLoad = false;

    // 3. Render tabel
    renderTable(orders);

    // 4. Paginasi
    renderPagination(pagination);

    // 5. Auto-select pesanan pertama jika belum ada yang dipilih (hanya di desktop)
    if (orders.length > 0) {
      if (selectedOrderCode) {
        // Cek apakah pesanan terpilih masih ada di list
        const exists = orders.some(function (o) { return o.order_code === selectedOrderCode; });
        if (exists) {
          highlightSelectedRow(selectedOrderCode);
        } else if (window.innerWidth >= 1024) {
          selectOrder(orders[0].order_code, false);
        } else {
          closeTerminalPanel();
        }
      } else if (window.innerWidth >= 1024) {
        // Auto-pilih baris pertama untuk workflow cepat
        selectOrder(orders[0].order_code, false);
      }
    } else {
      closeTerminalPanel();
    }
  }

  function updateTabCounts(counts) {
    $('#tab-count-pending').text(counts.pending || 0);
    $('#tab-count-processing').text(counts.processing || 0);
    $('#tab-count-ready').text(counts.ready || 0);
    $('#tab-count-completed').text(counts.completed || 0);
    $('#tab-count-cancelled').text(counts.cancelled || 0);
  }

  /* ============================================================
     RENDER TABEL PESANAN (KOLOM KIRI)
     ============================================================ */
  function renderTable(orders) {
    const $tbody = $('#orders-table-body').empty();

    if (!orders.length) {
      $('#table-empty').removeClass('hidden');
      $('#table-pagination').addClass('hidden');
      return;
    }

    $('#table-empty').addClass('hidden');

    orders.forEach(function (order) {
      const code    = $('<div>').text(order.order_code).html();
      const name    = $('<div>').text(order.customer_name).html();
      const summary = $('<div>').text(order.items_summary || '').html();
      const total   = window.Fmt.rupiah(order.total);
      const time    = timeAgo(order.created_at);

      let typeBadge = '';
      if (order.order_type === 'dine_in') {
        typeBadge = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-surface text-ink border border-line">Meja ${order.table_number || '-'}</span>`;
      } else {
        typeBadge = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-surface text-subtle border border-line">Takeaway</span>`;
      }

      const statusBadge = window.Fmt.statusBadge(order.status);

      // Tombol Aksi Cepat per status
      let quickAction = '';
      if (order.status === 'pending' && order.payment_status === 'unpaid') {
        quickAction = `
          <button type="button" class="quick-action-btn qa-pay h-7 px-3 rounded-full bg-accent text-white text-[11px] font-bold hover:opacity-85 transition-colors inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shadow-sm" title="Buka terminal pembayaran">
            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Bayar
          </button>`;
      } else if (order.status === 'processing') {
        quickAction = `
          <button type="button" class="quick-action-btn qa-ready h-7 px-3 rounded-full bg-black text-white text-[11px] font-bold hover:bg-neutral-800 transition-colors inline-flex items-center gap-1 cursor-pointer whitespace-nowrap" title="Tandai pesanan siap saji">
            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Siap
          </button>`;
      } else if (order.status === 'ready') {
        quickAction = `
          <button type="button" class="quick-action-btn qa-complete h-7 px-3 rounded-full bg-accent text-white text-[11px] font-bold hover:opacity-85 transition-colors inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shadow-sm" title="Tandai pesanan selesai">
            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            Selesai
          </button>`;
      } else if (order.status === 'completed') {
        quickAction = `
          <a href="${getBaseUrl()}cashier/receipt.php?code=${encodeURIComponent(order.order_code)}" target="_blank" class="quick-action-btn h-7 px-2.5 rounded-full bg-surface text-ink hover:bg-neutral-200 text-[11px] font-bold transition-colors inline-flex items-center gap-1 border border-line" title="Cetak struk">
            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Struk
          </a>`;
      }

      const $row = $(`
        <tr
          data-order-code="${code}"
          class="hover:bg-surface/70 transition-colors cursor-pointer group"
          title="Klik baris untuk membuka rincian pesanan"
        >
          <!-- Kode Pesanan -->
          <td class="py-3 px-3.5 sm:px-4 font-mono font-bold text-ink whitespace-nowrap">
            ${code}
          </td>

          <!-- Pelanggan & Meja -->
          <td class="py-3 px-3">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="font-bold text-ink">${name}</span>
              ${typeBadge}
            </div>
            <p class="text-[11px] text-subtle line-clamp-1 mt-0.5">${summary}</p>
          </td>

          <!-- Total -->
          <td class="py-3 px-3 text-right font-bold text-ink tabular-nums whitespace-nowrap">
            ${total}
          </td>

          <!-- Waktu Relatif -->
          <td class="py-3 px-3 text-[11px] text-subtle whitespace-nowrap" title="${order.created_at}">
            ${time}
          </td>

          <!-- Status Badge -->
          <td class="py-3 px-3 whitespace-nowrap">
            ${statusBadge}
          </td>

          <!-- Aksi Cepat -->
          <td class="py-3 px-3 sm:px-4 text-right whitespace-nowrap">
            ${quickAction}
          </td>
        </tr>
      `);

      $row.data('order-obj', order);
      $tbody.append($row);
    });

    if (selectedOrderCode) {
      highlightSelectedRow(selectedOrderCode);
    }
  }

  function highlightSelectedRow(code) {
    $('#orders-table-body tr').removeClass('is-selected');
    $(`#orders-table-body tr[data-order-code="${code}"]`).addClass('is-selected');
  }

  function renderPagination(p) {
    if (!p || p.total <= 0) {
      $('#table-pagination').addClass('hidden');
      return;
    }

    currentPage = p.page || 1;
    totalPages  = p.pages || 1;

    $('#pagination-info').text(`Total ${p.total} pesanan (Hal. ${currentPage} dari ${totalPages})`);
    $('#current-page-text').text(currentPage);

    $('#btn-prev-page').prop('disabled', currentPage <= 1);
    $('#btn-next-page').prop('disabled', currentPage >= totalPages);

    $('#table-pagination').removeClass('hidden');
  }

  /* ============================================================
     SELECT ORDER & POPULATE POS TERMINAL PANEL (KOLOM KANAN)
     ============================================================ */
  function selectOrder(code, focusPaymentInput) {
    selectedOrderCode = code;
    highlightSelectedRow(code);

    // Tampilkan panel, sembunyikan placeholder kosong
    $('#pos-terminal-empty').addClass('hidden');
    $('#pos-terminal-panel').removeClass('hidden');

    // Di mobile: jadikan bottom sheet drawer
    if (window.innerWidth < 1024) {
      $('#pos-terminal-panel').addClass('mobile-open');
      $('#pos-terminal-backdrop').removeClass('hidden');
    }

    // Loading state di panel
    $('#terminal-order-code').text(code);
    $('#terminal-order-time').text('Memuat rincian pesanan...');
    $('#terminal-customer-name').text('—');
    $('#terminal-items-list').html('<p class="py-4 text-center text-xs text-subtle">Memuat item...</p>');
    $('#terminal-total-amount').text('Rp 0');

    // Ambil detail lengkap pesanan via API
    $.ajax({
      url:      getBaseUrl() + 'api/orders/detail.php',
      type:     'GET',
      data:     { code: code },
      dataType: 'json'
    })
    .done(function (res) {
      if (res && res.success && res.data) {
        populateTerminalPanel(res.data, focusPaymentInput);
      } else {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('error', res.message || 'Gagal memuat detail pesanan.');
        }
      }
    })
    .fail(function (jqXHR) {
      const res = jqXHR.responseJSON;
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', (res && res.message) ? res.message : 'Terjadi kesalahan saat memuat rincian.');
      }
    });
  }

  function populateTerminalPanel(data, focusPaymentInput) {
    const order = data.order || {};
    const items = data.items || [];
    currentSelectedOrder = order;

    // Header Panel
    $('#terminal-order-code').text(order.order_code);
    $('#terminal-order-time').text(window.Fmt.datetime(order.created_at) + ' (' + timeAgo(order.created_at) + ')');

    let typeStr = '';
    if (order.order_type === 'dine_in') {
      typeStr = `<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-ink text-white">Meja ${order.table_number || '-'}</span>`;
    } else {
      typeStr = `<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-surface text-subtle border border-line">Bawa Pulang</span>`;
    }
    $('#terminal-order-type-badge').html(typeStr);

    $('#terminal-customer-name').text(order.customer_name || 'Pelanggan');
    $('#terminal-payment-badge').html(window.Fmt.statusBadge(order.payment_status));

    // Catatan Khusus
    if (order.notes && order.notes.trim() !== '') {
      $('#terminal-notes').text(order.notes);
      $('#terminal-notes-box').removeClass('hidden');
    } else {
      $('#terminal-notes-box').addClass('hidden');
    }

    // Daftar Item
    $('#terminal-items-count').text(items.length + ' item');
    const $itemsList = $('#terminal-items-list').empty();
    if (!items.length) {
      $itemsList.html('<p class="py-3 text-xs text-subtle text-center">Tidak ada item.</p>');
    } else {
      items.forEach(function (it) {
        const imgSrc = it.image_url || PLACEHOLDER;
        const $itemRow = $(`
          <div class="flex items-center gap-2.5 py-2">
            <img
              src="${$('<div>').text(imgSrc).html()}"
              alt="${$('<div>').text(it.menu_name).html()}"
              class="w-9 h-9 rounded-lg object-cover bg-surface border border-line shrink-0"
              onerror="this.src='${PLACEHOLDER}'"
            >
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-ink truncate">${$('<div>').text(it.menu_name).html()}</p>
              <p class="text-[11px] text-subtle tabular-nums">${window.Fmt.rupiah(it.price)} × ${it.qty}</p>
            </div>
            <p class="text-xs font-bold text-ink tabular-nums shrink-0">${window.Fmt.rupiah(it.subtotal)}</p>
          </div>
        `);
        $itemsList.append($itemRow);
      });
    }

    // Total Pembayaran
    const totalAmount = parseInt(order.total, 10) || 0;
    $('#terminal-total-amount').text(window.Fmt.rupiah(totalAmount));

    // Render Aksi Berdasarkan Status
    renderTerminalActions(order, totalAmount, focusPaymentInput);
  }

  function renderTerminalActions(order, totalAmount, focusPaymentInput) {
    // Sembunyikan semua mode terlebih dahulu
    $('#terminal-pay-mode').addClass('hidden');
    $('#terminal-processing-mode').addClass('hidden');
    $('#terminal-ready-mode').addClass('hidden');
    $('#terminal-completed-mode').addClass('hidden');
    $('#terminal-cancelled-mode').addClass('hidden');

    const receiptUrl = getBaseUrl() + 'cashier/receipt.php?code=' + encodeURIComponent(order.order_code);

    // 1. Status 'pending' & belum dibayar
    if (order.status === 'pending' && order.payment_status === 'unpaid') {
      $('#terminal-pay-mode').removeClass('hidden');

      // Siapkan tombol nominal cepat
      setupQuickAmountButtons(totalAmount);

      // Reset ke Tunai
      setPaymentMethod('cash');

      // Reset input uang
      $('#terminal-cash-input').val('');
      updateChangeCalculation();

      if (focusPaymentInput) {
        setTimeout(function () {
          $('#terminal-cash-input').trigger('focus');
        }, 100);
      }
      return;
    }

    // 2. Status 'processing' (sudah dibayar, disiapkan dapur)
    if (order.status === 'processing') {
      $('#terminal-processing-mode').removeClass('hidden');
      $('#btn-terminal-print-receipt-proc').attr('href', receiptUrl);

      if (userRole === 'admin') {
        $('#btn-terminal-cancel-admin-proc').removeClass('hidden');
      } else {
        $('#btn-terminal-cancel-admin-proc').addClass('hidden');
      }
      return;
    }

    // 3. Status 'ready' (siap saji)
    if (order.status === 'ready') {
      $('#terminal-ready-mode').removeClass('hidden');
      $('#btn-terminal-print-receipt-ready').attr('href', receiptUrl);
      return;
    }

    // 4. Status 'completed' (selesai & lunas)
    if (order.status === 'completed') {
      $('#terminal-completed-mode').removeClass('hidden');
      $('#btn-terminal-print-receipt-done').attr('href', receiptUrl);
      return;
    }

    // 5. Status 'cancelled' (batal)
    if (order.status === 'cancelled') {
      let cancelReason = order.cancel_reason || 'Tidak ada keterangan pembatalan.';
      if (order.cancelled_by_name) {
        cancelReason += ' (Dibatalkan oleh: ' + order.cancelled_by_name + ')';
      }
      $('#terminal-cancel-reason').text(cancelReason);
      $('#terminal-cancelled-mode').removeClass('hidden');
      return;
    }
  }

  function closeTerminalPanel() {
    selectedOrderCode = null;
    currentSelectedOrder = null;
    $('#orders-table-body tr').removeClass('is-selected');
    $('#pos-terminal-panel').addClass('hidden').removeClass('mobile-open');
    $('#pos-terminal-backdrop').addClass('hidden');
    $('#pos-terminal-empty').removeClass('hidden');
  }

  /* ============================================================
     TERMINAL PEMBAYARAN: TUNAI & QRIS (FR-PAY-01..04)
     ============================================================ */
  function setPaymentMethod(method) {
    currentPaymentMethod = method;
    if (method === 'cash') {
      $('#terminal-method-cash')
        .addClass('bg-black text-white shadow-sm')
        .removeClass('text-subtle');
      $('#terminal-method-qris')
        .removeClass('bg-black text-white shadow-sm')
        .addClass('text-subtle');

      $('#terminal-cash-section').removeClass('hidden');
      $('#terminal-qris-section').addClass('hidden');

      setTimeout(function () {
        $('#terminal-cash-input').trigger('focus');
      }, 50);
    } else {
      $('#terminal-method-qris')
        .addClass('bg-black text-white shadow-sm')
        .removeClass('text-subtle');
      $('#terminal-method-cash')
        .removeClass('bg-black text-white shadow-sm')
        .addClass('text-subtle');

      $('#terminal-cash-section').addClass('hidden');
      $('#terminal-qris-section').removeClass('hidden');
    }
  }

  function setupQuickAmountButtons(total) {
    const $container = $('#terminal-quick-amounts').empty();
    const buttons = [];
    const set = new Set();

    // 1. Selalu ada Uang Pas
    buttons.push({ label: 'Uang Pas (' + window.Fmt.rupiah(total) + ')', amount: total });
    set.add(total);

    // Langkah pembulatan awal terdekat sesuai nilai total
    let step = 1000;
    if (total >= 500000) step = 10000;
    else if (total >= 100000) step = 5000;
    else if (total >= 20000) step = 2000;
    else step = 1000;

    let candidate = Math.ceil(total / step) * step;
    if (candidate <= total) candidate += step;

    // Loop dinamis hingga terkumpul tepat 5 opsi nominal
    while (buttons.length < 5) {
      if (!set.has(candidate) && candidate > total) {
        set.add(candidate);
        buttons.push({ label: window.Fmt.rupiah(candidate), amount: candidate });
      }

      // Kenaikan bertahap pecahan uang Rupiah
      if (candidate >= 1000000) {
        candidate += 100000;
      } else if (candidate >= 500000) {
        if (buttons.length === 2) candidate += 10000;
        else if (buttons.length === 3) candidate += 30000;
        else candidate += 50000;
      } else if (candidate >= 200000) {
        candidate += (buttons.length <= 2 ? 20000 : 50000);
      } else if (candidate >= 100000) {
        candidate += (buttons.length <= 2 ? 10000 : 50000);
      } else if (candidate >= 50000) {
        candidate += (buttons.length <= 2 ? 10000 : 20000);
      } else {
        candidate += 5000;
      }
    }

    buttons.forEach(function (item) {
      $('<button>')
        .attr('type', 'button')
        .addClass('terminal-quick-pill h-7 px-2.5 rounded-lg bg-surface hover:bg-neutral-200 text-[11px] font-bold text-ink transition-colors cursor-pointer border border-line')
        .attr('data-amount', item.amount)
        .text(item.label)
        .appendTo($container);
    });
  }

  function updateChangeCalculation() {
    if (!currentSelectedOrder) return;
    const total      = parseInt(currentSelectedOrder.total, 10) || 0;
    const rawInput   = $('#terminal-cash-input').val().replace(/\D/g, '');
    const amountPaid = parseInt(rawInput, 10) || 0;
    const diff       = amountPaid - total;

    const $box       = $('#terminal-change-box');
    const $label     = $('#terminal-change-label');
    const $val       = $('#terminal-change-amount');
    const $btnPay    = $('#btn-terminal-submit-cash');

    if (amountPaid <= 0) {
      $label.text('Kembalian:');
      $val.text('Rp 0').removeClass('text-danger text-accent');
      $btnPay.prop('disabled', false).removeClass('opacity-40 cursor-not-allowed');
      return;
    }

    if (diff < 0) {
      // Kurang bayar
      $label.text('Kurang:');
      $val.text(window.Fmt.rupiah(Math.abs(diff))).addClass('text-danger').removeClass('text-accent');
      $btnPay.prop('disabled', true).addClass('opacity-40 cursor-not-allowed');
    } else {
      // Uang pas / kembalian cukup
      $label.text('Kembalian:');
      $val.text(window.Fmt.rupiah(diff)).addClass('text-accent').removeClass('text-danger');
      $btnPay.prop('disabled', false).removeClass('opacity-40 cursor-not-allowed');
    }
  }

  function processCashPayment() {
    if (!currentSelectedOrder) return;
    const total    = parseInt(currentSelectedOrder.total, 10) || 0;
    const rawInput = $('#terminal-cash-input').val().replace(/\D/g, '');
    const amount   = parseInt(rawInput, 10) || 0;

    if (amount < total) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', 'Nominal bayar kurang dari total pesanan.');
      }
      $('#terminal-cash-input').focus();
      return;
    }

    executePayment(currentSelectedOrder, 'cash', amount, $('#btn-terminal-submit-cash'));
  }

  function processQrisPayment() {
    if (!currentSelectedOrder) return;
    const total = parseInt(currentSelectedOrder.total, 10) || 0;
    executePayment(currentSelectedOrder, 'qris', total, $('#btn-terminal-submit-qris'));
  }

  function executePayment(order, method, amountPaid, $btn) {
    if (window.UI && typeof window.UI.setLoading === 'function') {
      window.UI.setLoading($btn, true);
    } else {
      $btn.prop('disabled', true);
    }

    $.ajax({
      url:         getBaseUrl() + 'api/payments/pay.php',
      type:        'POST',
      contentType: 'application/json',
      data:        JSON.stringify({
        order_id:    order.id,
        method:      method,
        amount_paid: amountPaid
      }),
      dataType:    'json',
      headers:     { 'X-CSRF-Token': getCsrfToken() }
    })
    .done(function (res) {
      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, false);
      } else {
        $btn.prop('disabled', false);
      }

      if (res && res.success && res.data) {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('success', 'Pembayaran berhasil! Kode: ' + res.data.payment_code);
        }

        // Tawarkan struk instan
        offerPrintReceipt(order.order_code, res.data);

        // Segarkan data pesanan
        fetchOrders(false);

        // Muat ulang rincian pesanan yang baru dibayar
        selectOrder(order.order_code, false);
      } else {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('error', res.message || 'Gagal memproses pembayaran.');
        }
      }
    })
    .fail(function (jqXHR) {
      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, false);
      } else {
        $btn.prop('disabled', false);
      }
      const res = jqXHR.responseJSON;
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', (res && res.message) ? res.message : 'Terjadi kesalahan saat memproses pembayaran.');
      }
    });
  }

  /* ============================================================
     UPDATE STATUS CEPAT (1-KLIK)
     ============================================================ */
  function quickUpdateStatus(order, nextStatus, $btn) {
    const labelMap = {
      ready: 'Siap Saji',
      completed: 'Selesai'
    };
    const nextLabel = labelMap[nextStatus] || nextStatus;

    $btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

    window.Api.post(getBaseUrl() + 'api/orders/update-status.php', {
      order_id: order.id,
      next_status: nextStatus
    })
    .then(function () {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('success', order.order_code + ' → ' + nextLabel);
      }
      fetchOrders(false);
      // Jika pesanan yang diubah adalah yang sedang dibuka di terminal, perbarui panelnya
      if (selectedOrderCode === order.order_code) {
        selectOrder(order.order_code, false);
      }
    })
    .catch(function (err) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', err.message || 'Gagal mengubah status.');
      }
      $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    });
  }

  /* ============================================================
     MODAL BATALKAN PESANAN (FR-STS-02, FR-STS-03)
     ============================================================ */
  function openCancelModal(order) {
    pendingCancelOrder = order;
    $('#cancel-modal-order-code').text(order.order_code);
    $('#cancel-reason-input').val('');
    $('#cancel-reason-error').addClass('hidden').text('');

    const isPaid = (order.payment_status === 'paid');
    if (isPaid) {
      $('#cancel-modal-void-warning').removeClass('hidden');
      $('#cancel-modal-description').text(
        'Pesanan ini sudah dibayar (' + window.Fmt.rupiah(order.total) + '). Pembatalan akan otomatis membatalkan/void transaksi pembayaran.'
      );
      $('#cancel-reason-required-badge').removeClass('hidden').text('* (wajib min. 5 karakter)');
    } else {
      $('#cancel-modal-void-warning').addClass('hidden');
      $('#cancel-modal-description').text(
        'Pesanan ini belum dibayar. Apakah Anda yakin ingin membatalkannya?'
      );
      $('#cancel-reason-required-badge').removeClass('hidden').text('(opsional)');
    }

    $('#modal-cancel-order').removeClass('hidden');
    setTimeout(function () {
      $('#cancel-reason-input').focus();
    }, 100);
  }

  function handleCancelSubmit($btn) {
    if (!pendingCancelOrder) return;

    const order = pendingCancelOrder;
    const reason = $('#cancel-reason-input').val().trim();
    const isPaid = (order.payment_status === 'paid');

    if (isPaid && reason.length < 5) {
      $('#cancel-reason-error')
        .removeClass('hidden')
        .text('Alasan pembatalan wajib diisi minimal 5 karakter.');
      $('#cancel-reason-input').focus();
      return;
    }

    if (window.UI && typeof window.UI.setLoading === 'function') {
      window.UI.setLoading($btn, true);
    } else {
      $btn.prop('disabled', true);
    }

    window.Api.post(getBaseUrl() + 'api/orders/cancel.php', {
      order_id: order.id,
      reason: reason
    })
    .then(function (res) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('success', res.message || ('Pesanan ' + order.order_code + ' berhasil dibatalkan.'));
      }
      $('#modal-cancel-order').addClass('hidden');
      closeTerminalPanel();
      fetchOrders(false);
    })
    .catch(function (err) {
      $('#cancel-reason-error')
        .removeClass('hidden')
        .text(err.message || 'Gagal membatalkan pesanan.');
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', err.message || 'Gagal membatalkan pesanan.');
      }
    })
    .finally(function () {
      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, false);
      } else {
        $btn.prop('disabled', false);
      }
    });
  }

  /* ============================================================
     TAWARAN CETAK STRUK INSTAN
     ============================================================ */
  function offerPrintReceipt(orderCode, paymentResult) {
    const receiptUrl = getBaseUrl() + 'cashier/receipt.php?code=' + encodeURIComponent(orderCode);

    const $toast = $('<div>')
      .css({
        position: 'fixed', bottom: '24px', left: '50%', transform: 'translateX(-50%)',
        background: '#000', color: '#fff', padding: '10px 18px',
        borderRadius: '9999px', fontSize: '13px', fontWeight: '500',
        zIndex: 9999, whiteSpace: 'nowrap', boxShadow: '0 4px 24px rgba(0,0,0,.25)',
        display: 'flex', alignItems: 'center', gap: '12px'
      })
      .html(
        '<span>✓ Pembayaran ' + orderCode + ' Sukses!</span>' +
        '<a href="' + receiptUrl + '" target="_blank" style="color:#06C167;text-decoration:underline;font-weight:700;">Cetak Struk ↗</a>'
      );

    $('body').append($toast);
    setTimeout(function () {
      $toast.fadeOut(400, function () { $(this).remove(); });
    }, 6000);
  }

  /* ============================================================
     AUTO-REFRESH (FR-COR-03)
     ============================================================ */
  function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshTimer = setInterval(function () {
      if (document.hidden) return;
      fetchOrders(true);
    }, 8000);
  }

  function stopAutoRefresh() {
    if (autoRefreshTimer) {
      clearInterval(autoRefreshTimer);
      autoRefreshTimer = null;
    }
  }

  function setupVisibilityWatcher() {
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) {
        $('#auto-refresh-status').text('Auto-refresh 8s');
        fetchOrders(true);
        startAutoRefresh();
      } else {
        $('#auto-refresh-status').text('Auto-refresh dijeda');
        stopAutoRefresh();
      }
    });
  }

  /* ============================================================
     STATE UI (LOADING & ERROR)
     ============================================================ */
  function showLoading(show) {
    if (show) {
      $('#table-loading').removeClass('hidden');
      $('#orders-table-body').addClass('hidden');
      $('#table-empty').addClass('hidden');
    } else {
      $('#table-loading').addClass('hidden');
      $('#orders-table-body').removeClass('hidden');
    }
  }

  function showError(msg) {
    $('#table-error-text').text(msg || 'Gagal memuat pesanan.');
    $('#table-error').removeClass('hidden');
    $('#orders-table-body').empty().addClass('hidden');
    $('#table-pagination').addClass('hidden');
  }

  /* Start */
  init();
});
