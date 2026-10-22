/**
 * Script Halaman Pesanan Masuk Kasir.
 * FR-COR-01..04, FR-PAY-01..04, prd.md §6.9, §6.11, §12.3, §12.4.
 *
 * Fitur:
 * 1. Tab status (Belum dibayar, Diproses, Siap, Selesai, Batal) + jumlah pesanan per tab.
 * 2. Pencarian kode/nama (debounce 300 ms), filter tipe, dan filter tanggal.
 * 3. Tabel pesanan dengan klik baris untuk membuka modal detail.
 * 4. Modal detail pesanan (item, catatan, status, total, tombol aksi per status).
 * 5. Auto-refresh tiap 8 detik (berhenti saat document.hidden) + indikator pesanan baru.
 * 6. Integrasi payment.js — tombol Bayar membuka PaymentModal; setelah sukses refresh + tawaran struk.
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
  let currentStatus    = 'pending';
  let currentPage       = 1;
  let totalPages        = 1;
  let searchTimer       = null;
  let autoRefreshTimer  = null;
  let isRequestPending  = false;
  let knownOrderCodes   = new Set();
  let isInitialLoad     = true;
  let pendingCancelOrder = null;
  const userRole        = $('#cashier-orders-page').data('user-role') || 'cashier';

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

    // 10. Klik Baris Tabel / Tombol Aksi -> Buka Modal Detail
    $('#orders-table-body').on('click', 'tr, .btn-view-detail', function (e) {
      const $tr = $(this).closest('tr');
      const code = $tr.attr('data-order-code') || $tr.data('order-code');
      if (code) {
        openOrderDetail(code);
      }
    });

    $(document).on('click', '[data-modal-dismiss]', function () {
      const modal = $(this).closest('.fixed[role="dialog"]');
      const modalId = modal.length ? modal.attr('id') : 'modal-order-detail';
      if (window.UI && typeof window.UI.closeModal === 'function') {
        window.UI.closeModal(modalId);
      } else {
        $('#' + modalId).addClass('hidden');
      }
    });

    // 12. Konfirmasi Pembatalan Pesanan Submit
    $('#btn-confirm-cancel-submit').on('click', function () {
      handleCancelSubmit($(this));
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

    // 1. Perbarui jumlah di setiap tab
    updateTabCounts(tabCounts);

    // 2. Deteksi pesanan baru jika ini background auto-refresh
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

    // Perbarui daftar kode yang telah diketahui
    orders.forEach(function (o) {
      knownOrderCodes.add(o.order_code);
    });
    isInitialLoad = false;

    // 3. Render tabel pesanan
    renderTable(orders);

    // 4. Render Paginasi
    renderPagination(pagination);
  }

  /* ============================================================
     UPDATE TAB COUNTS
     ============================================================ */
  function updateTabCounts(counts) {
    $('#tab-count-pending').text(counts.pending || 0);
    $('#tab-count-processing').text(counts.processing || 0);
    $('#tab-count-ready').text(counts.ready || 0);
    $('#tab-count-completed').text(counts.completed || 0);
    $('#tab-count-cancelled').text(counts.cancelled || 0);
  }

  /* ============================================================
     RENDER TABEL PESANAN
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
        typeBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface text-ink">Meja ${order.table_number || '-'}</span>`;
      } else {
        typeBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface text-subtle">Takeaway</span>`;
      }

      const statusBadge = window.Fmt.statusBadge(order.status);

      const $row = $(`
        <tr
          data-order-code="${code}"
          class="hover:bg-surface/60 transition-colors cursor-pointer group"
          title="Klik untuk melihat detail pesanan"
        >
          <!-- Kode Pesanan -->
          <td class="py-3 px-4 sm:px-6 font-mono font-bold text-ink whitespace-nowrap">
            ${code}
          </td>

          <!-- Pelanggan & Item Summary -->
          <td class="py-3 px-4">
            <p class="font-medium text-ink">${name}</p>
            <p class="text-xs text-subtle line-clamp-1 mt-0.5">${summary}</p>
          </td>

          <!-- Tipe / Meja -->
          <td class="py-3 px-4 whitespace-nowrap">
            ${typeBadge}
          </td>

          <!-- Total -->
          <td class="py-3 px-4 text-right font-bold text-ink tabular-nums whitespace-nowrap">
            ${total}
          </td>

          <!-- Waktu Relatif -->
          <td class="py-3 px-4 text-xs text-subtle whitespace-nowrap" title="${order.created_at}">
            ${time}
          </td>

          <!-- Status Badge -->
          <td class="py-3 px-4 whitespace-nowrap">
            ${statusBadge}
          </td>

          <!-- Aksi / Ikon Panah -->
          <td class="py-3 px-4 sm:px-6 text-center whitespace-nowrap">
            <button type="button" class="btn-view-detail inline-flex items-center justify-center w-7 h-7 rounded-full group-hover:bg-black group-hover:text-white text-subtle transition-colors cursor-pointer" title="Lihat detail pesanan">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </td>
        </tr>
      `);

      $tbody.append($row);
    });
  }

  /* ============================================================
     PAGINASI
     ============================================================ */
  function renderPagination(p) {
    if (!p || p.total <= 0) {
      $('#table-pagination').addClass('hidden');
      return;
    }

    currentPage = p.page || 1;
    totalPages  = p.pages || 1;

    $('#pagination-info').text(`Menampilkan ${p.total} pesanan (Halaman ${currentPage} dari ${totalPages})`);
    $('#current-page-text').text(currentPage);

    $('#btn-prev-page').prop('disabled', currentPage <= 1);
    $('#btn-next-page').prop('disabled', currentPage >= totalPages);

    $('#table-pagination').removeClass('hidden');
  }

  /* ============================================================
     DETAIL MODAL (FR-COR-04)
     ============================================================ */
  function openOrderDetail(code) {
    // Tampilkan modal dan state loading awal
    $('#modal-order-code').text(code);
    $('#modal-order-time').text('Memuat detail...');
    $('#modal-items-list').html('<p class="py-4 text-center text-xs text-subtle">Memuat rincian item...</p>');
    
    if (window.UI && typeof window.UI.openModal === 'function') {
      window.UI.openModal('modal-order-detail');
    } else {
      $('#modal-order-detail').removeClass('hidden');
    }

    $.ajax({
      url:      getBaseUrl() + 'api/orders/detail.php',
      type:     'GET',
      data:     { code: code },
      dataType: 'json'
    })
    .done(function (res) {
      if (res && res.success && res.data) {
        populateDetailModal(res.data);
      } else {
        alert(res.message || 'Gagal memuat detail pesanan.');
        if (window.UI && typeof window.UI.closeModal === 'function') {
          window.UI.closeModal('modal-order-detail');
        } else {
          $('#modal-order-detail').addClass('hidden');
        }
      }
    })
    .fail(function (jqXHR) {
      const res = jqXHR.responseJSON;
      alert((res && res.message) ? res.message : 'Gagal memuat detail pesanan.');
      if (window.UI && typeof window.UI.closeModal === 'function') {
        window.UI.closeModal('modal-order-detail');
      } else {
        $('#modal-order-detail').addClass('hidden');
      }
    });
  }

  function populateDetailModal(data) {
    const order = data.order || {};
    const items = data.items || [];

    $('#modal-order-code').text(order.order_code);
    $('#modal-order-time').text(window.Fmt.datetime(order.created_at) + ' (' + timeAgo(order.created_at) + ')');
    $('#modal-customer-name').text(order.customer_name);

    let typeStr = order.order_type === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang';
    if (order.order_type === 'dine_in' && order.table_number) {
      typeStr += ' (Meja ' + order.table_number + ')';
    }
    $('#modal-order-type').text(typeStr);

    $('#modal-status-badge').html(window.Fmt.statusBadge(order.status));
    $('#modal-pay-badge').html(window.Fmt.statusBadge(order.payment_status));

    // Catatan
    if (order.notes && order.notes.trim() !== '') {
      $('#modal-notes').text(order.notes);
      $('#modal-notes-box').removeClass('hidden');
    } else {
      $('#modal-notes-box').addClass('hidden');
    }

    // Alasan pembatalan
    if (order.status === 'cancelled') {
      let cancelText = order.cancel_reason || 'Tidak ada keterangan pembatalan.';
      if (order.cancelled_by_name) {
        cancelText += ' (Dibatalkan oleh: ' + order.cancelled_by_name + ')';
      }
      $('#modal-cancel-reason').text(cancelText);
      $('#modal-cancel-box').removeClass('hidden');
    } else {
      $('#modal-cancel-box').addClass('hidden');
    }

    // Render daftar item
    const $itemsList = $('#modal-items-list').empty();
    if (!items.length) {
      $itemsList.html('<p class="py-3 text-xs text-subtle text-center">Tidak ada item.</p>');
    } else {
      items.forEach(function (it) {
        const imgSrc = it.image_url || PLACEHOLDER;
        const $itemRow = $(`
          <div class="flex items-center gap-3 py-2.5">
            <img
              src="${$('<div>').text(imgSrc).html()}"
              alt="${$('<div>').text(it.menu_name).html()}"
              class="w-10 h-10 rounded-lg object-cover bg-surface border border-line shrink-0"
              onerror="this.src='${PLACEHOLDER}'"
            >
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-ink line-clamp-1">${$('<div>').text(it.menu_name).html()}</p>
              <p class="text-[11px] text-subtle tabular-nums">${window.Fmt.rupiah(it.price)} × ${it.qty}</p>
            </div>
            <p class="text-xs font-bold text-ink tabular-nums shrink-0">${window.Fmt.rupiah(it.subtotal)}</p>
          </div>
        `);
        $itemsList.append($itemRow);
      });
    }

    // Total
    $('#modal-total-amount').text(window.Fmt.rupiah(order.total));

    // Extensible Action Buttons (untuk P12: Bayar, P13: Ubah Status)
    renderActionButtons(order);
  }

  function renderActionButtons(order) {
    const $actions = $('#modal-action-buttons').empty();
    const btnBase = 'flex-1 h-10 rounded-full text-xs font-bold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer';

    // 1. Pending (Belum Dibayar)
    if (order.status === 'pending' && order.payment_status === 'unpaid') {
      // Tombol Bayar
      const $btnPay = $(`
        <button type="button" id="btn-modal-pay" class="${btnBase} bg-black text-white hover:bg-neutral-800">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
            <line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          Bayar
        </button>
      `);

      $btnPay.on('click', function () {
        if (window.UI && typeof window.UI.closeModal === 'function') {
          window.UI.closeModal('modal-order-detail');
        } else {
          $('#modal-order-detail').addClass('hidden');
        }

        if (window.PaymentModal) {
          window.PaymentModal.open(order, {
            onSuccess: function (result) {
              fetchOrders(false);
              offerPrintReceipt(order.order_code, result);
            }
          });
        }
      });
      $actions.append($btnPay);

      // Tombol Batalkan (kasir & admin boleh)
      const $btnCancel = $(`
        <button type="button" class="${btnBase} bg-surface text-danger hover:bg-neutral-200">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
          Batalkan
        </button>
      `);
      $btnCancel.on('click', function () {
        openCancelModal(order);
      });
      $actions.append($btnCancel);

      return;
    }

    // 2. Processing (Diproses)
    if (order.status === 'processing') {
      // Tombol Tandai Siap
      const $btnReady = $(`
        <button type="button" id="btn-mark-ready" class="${btnBase} bg-black text-white hover:bg-neutral-800">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Tandai Siap
        </button>
      `);
      $btnReady.on('click', function () {
        handleUpdateStatus(order, 'ready', 'Siap Saji', $btnReady);
      });
      $actions.append($btnReady);

      // Tombol Cetak Struk
      $actions.append(buildReceiptButton(order));

      // Tombol Batalkan (Hanya Admin)
      if (userRole === 'admin') {
        const $btnCancel = $(`
          <button type="button" class="${btnBase} bg-surface text-danger hover:bg-neutral-200">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Batalkan
          </button>
        `);
        $btnCancel.on('click', function () {
          openCancelModal(order);
        });
        $actions.append($btnCancel);
      }

      return;
    }

    // 3. Ready (Siap Saji)
    if (order.status === 'ready') {
      // Tombol Tandai Selesai
      const $btnCompleted = $(`
        <button type="button" id="btn-mark-completed" class="${btnBase} bg-black text-white hover:bg-neutral-800">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M5 13l4 4L19 7"/>
          </svg>
          Tandai Selesai
        </button>
      `);
      $btnCompleted.on('click', function () {
        handleUpdateStatus(order, 'completed', 'Selesai', $btnCompleted);
      });
      $actions.append($btnCompleted);

      // Tombol Cetak Struk
      $actions.append(buildReceiptButton(order));

      // Tombol Batalkan (Hanya Admin)
      if (userRole === 'admin') {
        const $btnCancel = $(`
          <button type="button" class="${btnBase} bg-surface text-danger hover:bg-neutral-200">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Batalkan
          </button>
        `);
        $btnCancel.on('click', function () {
          openCancelModal(order);
        });
        $actions.append($btnCancel);
      }

      return;
    }

    // 4. Completed (Selesai)
    if (order.status === 'completed') {
      // Struk saja, tidak bisa dibatalkan
      $actions.append(buildReceiptButton(order));
      return;
    }
  }

  function buildReceiptButton(order) {
    const btnBase = 'flex-1 h-10 rounded-full text-xs font-bold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer';
    return $(`
      <a href="${getBaseUrl()}cashier/receipt.php?code=${encodeURIComponent(order.order_code)}"
         target="_blank"
         class="${btnBase} bg-surface text-ink hover:bg-neutral-200"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Cetak Struk
      </a>
    `);
  }

  function handleUpdateStatus(order, nextStatus, nextLabel, $btn) {
    const confirmMsg = 'Ubah status pesanan ' + order.order_code + ' menjadi ' + nextLabel + '?';
    const confirmPromise = (window.UI && typeof window.UI.confirm === 'function')
      ? window.UI.confirm(confirmMsg)
      : Promise.resolve(window.confirm(confirmMsg));

    confirmPromise.then(function (yes) {
      if (!yes) return;

      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, true);
      } else {
        $btn.prop('disabled', true);
      }

      window.Api.post(getBaseUrl() + 'api/orders/update-status.php', {
        order_id: order.id,
        next_status: nextStatus
      })
      .then(function (res) {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('success', 'Status pesanan ' + order.order_code + ' diubah ke ' + nextLabel + '.');
        }
        if (window.UI && typeof window.UI.closeModal === 'function') {
          window.UI.closeModal('modal-order-detail');
        } else {
          $('#modal-order-detail').addClass('hidden');
        }
        fetchOrders(false);
      })
      .catch(function (err) {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('error', err.message || 'Gagal mengubah status pesanan.');
        } else {
          alert(err.message || 'Gagal mengubah status pesanan.');
        }
      })
      .finally(function () {
        if (window.UI && typeof window.UI.setLoading === 'function') {
          window.UI.setLoading($btn, false);
        } else {
          $btn.prop('disabled', false);
        }
      });
    });
  }

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

    if (window.UI && typeof window.UI.openModal === 'function') {
      window.UI.openModal('modal-cancel-order');
    } else {
      $('#modal-cancel-order').removeClass('hidden');
    }
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
      if (window.UI && typeof window.UI.closeModal === 'function') {
        window.UI.closeModal('modal-cancel-order');
        window.UI.closeModal('modal-order-detail');
      } else {
        $('#modal-cancel-order').addClass('hidden');
        $('#modal-order-detail').addClass('hidden');
      }
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

  /* ── Tawaran Cetak Struk setelah bayar ──────────────────────── */
  function offerPrintReceipt(orderCode, paymentResult) {
    // Toast dengan tautan cetak struk
    const receiptUrl = getBaseUrl() + 'cashier/receipt.php?code=' + encodeURIComponent(orderCode);

    // Buka struk di tab baru (opsional — user bisa cetak dari sana)
    // Hanya tampilkan toast; user bisa klik dari modal atau langsung
    const $toast = $('<div>')
      .css({
        position: 'fixed', bottom: '24px', left: '50%', transform: 'translateX(-50%)',
        background: '#000', color: '#fff', padding: '12px 18px',
        borderRadius: '9999px', fontSize: '13px', fontWeight: '500',
        zIndex: 9999, whiteSpace: 'nowrap', boxShadow: '0 4px 24px rgba(0,0,0,.18)',
        display: 'flex', alignItems: 'center', gap: '12px'
      })
      .html(
        '<span>Pembayaran berhasil!</span>' +
        '<a href="' + receiptUrl + '" target="_blank" style="color:#fff;text-decoration:underline;font-weight:700;">Cetak Struk</a>'
      );

    $('body').append($toast);
    setTimeout(function () { $toast.fadeOut(400, function () { $(this).remove(); }); }, 5000);
  }

  /* ============================================================
     AUTO-REFRESH (FR-COR-03)
     Polling tiap 8 detik, berhenti saat document.hidden
     ============================================================ */
  function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshTimer = setInterval(function () {
      if (document.hidden) return; // Jangan refresh jika tab tidak aktif
      fetchOrders(true);
    }, 8000); // 8 detik
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
        // Tab aktif kembali: segera refresh sekali dan jalankan timer
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
     STATE UI
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
