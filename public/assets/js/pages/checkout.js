/**
 * Script Halaman Checkout Pelanggan.
 * FR-CHK-01..04, prd.md §6.7, BR-01..05.
 *
 * Alur:
 * 1. Baca CartStore — jika kosong redirect ke cart.php.
 * 2. Sinkronkan harga & ketersediaan dengan server (api/menus/list.php?ids[]=...).
 * 3. Render ringkasan pesanan di kartu "Pesanan Anda".
 * 4. Toggle tipe pesanan (dine_in/takeaway) menampilkan/menyembunyikan field meja.
 * 5. Validasi client sebelum submit.
 * 6. Kirim JSON ke api/orders/create.php (CSRF di header).
 * 7. Sukses → tampilkan layar sukses, kosongkan CartStore, perbarui URL.
 * 8. Gagal → tampilkan error per-field dari server.
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

  function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content') || '';
  }

  const PLACEHOLDER = 'assets/img/placeholder-menu.svg';
  const MAX_TABLE   = parseInt($('#checkout-page').data('max-table'), 10) || 30;

  /* ============================================================
     STATE
     ============================================================ */
  let cartItems       = [];   // Item yang sudah disinkron dengan server
  let orderType       = 'dine_in';
  let isSubmitting    = false;

  /* ============================================================
     INISIALISASI
     ============================================================ */
  function init() {
    const stored = CartStore.getItems();

    if (!stored || stored.length === 0) {
      // Keranjang kosong → redirect ke cart.php
      window.location.href = 'cart.php';
      return;
    }

    cartItems = stored.map(function (it) { return $.extend({}, it); });
    syncWithServer();
  }

  /* ============================================================
     SINKRONISASI SERVER
     ============================================================ */
  function syncWithServer() {
    showCartLoading(true);

    const ids    = cartItems.map(function (it) { return it.id; });
    const params = ids.map(function (id) { return 'ids[]=' + encodeURIComponent(id); }).join('&');
    const baseEl = document.querySelector('base');
    const base   = baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
    const url    = base + 'api/menus/list.php?' + params + '&include_unavailable=1&per_page=100';

    $.ajax({ url: url, type: 'GET', dataType: 'json' })
      .done(function (res) {
        if (res && res.data && res.data.data) {
          const serverMap = {};
          res.data.data.forEach(function (m) {
            serverMap[parseInt(m.id, 10)] = m;
          });

          cartItems.forEach(function (item) {
            const sm = serverMap[parseInt(item.id, 10)];
            if (sm) {
              item.name       = sm.name;
              item.price      = parseInt(sm.price, 10) || item.price;
              item.image_url  = sm.image_url || item.image_url || null;
              item.unavailable = !sm.is_available;
            } else {
              item.unavailable = true;
            }
          });
        }
        renderCartSummary();
      })
      .fail(function () {
        // Tampilkan dengan data lokal — server akan re-validasi
        renderCartSummary();
      });
  }

  /* ============================================================
     RENDER RINGKASAN PESANAN
     ============================================================ */
  function renderCartSummary() {
    showCartLoading(false);

    if (!cartItems.length) {
      $('#checkout-cart-empty').removeClass('hidden');
      $('#checkout-cart-items').addClass('hidden');
      $('#checkout-cart-total').addClass('hidden');
      $('#btn-submit-order').prop('disabled', true);
      return;
    }

    const $itemsEl  = $('#checkout-cart-items').empty();
    let total       = 0;
    let hasUnavail  = false;

    cartItems.forEach(function (item) {
      if (item.unavailable) { hasUnavail = true; return; }

      const lineTotal = (parseInt(item.price, 10) || 0) * (parseInt(item.qty, 10) || 1);
      total += lineTotal;

      const imgSrc = item.image_url || item.image || PLACEHOLDER;

      const $row = $(`
        <div class="flex items-center gap-3 py-2">
          <img
            src="${$('<div>').text(imgSrc).html()}"
            alt="${$('<div>').text(item.name).html()}"
            class="w-10 h-10 rounded-lg object-cover bg-surface border border-line shrink-0"
            onerror="this.src='${PLACEHOLDER}'"
          >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-ink line-clamp-1">${$('<div>').text(item.name).html()}</p>
            <p class="text-xs text-subtle tabular-nums">${formatRupiah(item.price)} × ${item.qty}</p>
          </div>
          <p class="text-sm font-bold text-ink tabular-nums shrink-0">${formatRupiah(lineTotal)}</p>
        </div>
      `);

      $itemsEl.append($row);
    });

    $itemsEl.removeClass('hidden');
    $('#checkout-cart-total').removeClass('hidden');
    $('#checkout-total-amount').text(formatRupiah(total));

    if (hasUnavail) {
      $('#checkout-unavailable-banner').removeClass('hidden');
      $('#btn-submit-order').prop('disabled', true);
    } else {
      $('#checkout-unavailable-banner').addClass('hidden');
      $('#btn-submit-order').prop('disabled', false);
    }
  }

  function showCartLoading(show) {
    if (show) {
      $('#checkout-cart-loading').removeClass('hidden');
      $('#checkout-cart-items').addClass('hidden');
      $('#checkout-cart-total').addClass('hidden');
    } else {
      $('#checkout-cart-loading').addClass('hidden');
    }
  }

  /* ============================================================
     TOGGLE TIPE PESANAN
     ============================================================ */
  $('#order-type-toggle').on('click', '.order-type-btn', function () {
    const val = $(this).data('value');
    orderType  = val;

    // Update tombol aktif
    $('.order-type-btn').each(function () {
      const isActive = $(this).data('value') === val;
      $(this)
        .toggleClass('bg-white shadow-sm text-ink', isActive)
        .toggleClass('text-subtle', !isActive)
        .attr('aria-checked', isActive ? 'true' : 'false');
    });

    // Tampilkan / sembunyikan field nomor meja
    if (val === 'dine_in') {
      $('#table-number-group').removeClass('hidden');
    } else {
      $('#table-number-group').addClass('hidden');
      $('#input-table-number').val('');
      clearFieldError('table-number');
    }
  });

  /* ============================================================
     VALIDASI CLIENT
     ============================================================ */
  function clearFieldError(field) {
    $('#error-' + field).addClass('hidden').text('');
    $('#input-' + field.replace(/-/g, '-')).removeClass('border-danger focus:border-danger');
  }

  function showFieldError(field, msg) {
    $('#error-' + field).removeClass('hidden').text(msg);
  }

  function validateForm() {
    let valid = true;

    // Reset semua error
    ['order-type', 'customer-name', 'table-number', 'notes'].forEach(clearFieldError);
    $('#checkout-server-error').addClass('hidden');

    // Nama
    const name = $('#input-customer-name').val().trim();
    if (name === '') {
      showFieldError('customer-name', 'Nama pelanggan wajib diisi.');
      valid = false;
    } else if (name.length < 2) {
      showFieldError('customer-name', 'Nama minimal 2 karakter.');
      valid = false;
    } else if (name.length > 50) {
      showFieldError('customer-name', 'Nama maksimal 50 karakter.');
      valid = false;
    }

    // Nomor meja (hanya dine_in)
    if (orderType === 'dine_in') {
      const tn = parseInt($('#input-table-number').val(), 10);
      if (!tn || tn < 1 || tn > MAX_TABLE) {
        showFieldError('table-number', 'Pilih nomor meja yang valid.');
        valid = false;
      }
    }

    // Catatan
    const notes = $('#input-notes').val().trim();
    if (notes.length > 255) {
      showFieldError('notes', 'Catatan maksimal 255 karakter.');
      valid = false;
    }

    // Item harus ada
    const validItems = cartItems.filter(function (it) { return !it.unavailable; });
    if (validItems.length === 0) {
      valid = false;
    }

    return valid;
  }

  /* ============================================================
     SUBMIT PESANAN
     ============================================================ */
  $('#btn-submit-order').on('click', function () {
    if (isSubmitting) return; // Anti double-submit
    if (!validateForm()) return;

    isSubmitting = true;
    setSubmitting(true);

    // Bangun payload: hanya menu_id dan qty (tidak ada price/total — BR-01)
    const validItems = cartItems.filter(function (it) { return !it.unavailable; });
    const items = validItems.map(function (it) {
      return { menu_id: parseInt(it.id, 10), qty: parseInt(it.qty, 10) };
    });

    const payload = {
      customer_name: $('#input-customer-name').val().trim(),
      order_type:    orderType,
      notes:         $('#input-notes').val().trim() || null,
      items:         items
    };

    if (orderType === 'dine_in') {
      payload.table_number = parseInt($('#input-table-number').val(), 10);
    }

    const baseEl = document.querySelector('base');
    const base   = baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';

    $.ajax({
      url:         base + 'api/orders/create.php',
      type:        'POST',
      contentType: 'application/json; charset=utf-8',
      dataType:    'json',
      data:        JSON.stringify(payload),
      headers:     { 'X-CSRF-Token': getCsrfToken() }
    })
    .done(function (res) {
      if (res && res.success && res.data) {
        onOrderSuccess(res.data.order_code, res.data.total);
      } else {
        showServerError(res.message || 'Terjadi kesalahan. Coba lagi.');
        setSubmitting(false);
        isSubmitting = false;
      }
    })
    .fail(function (jqXHR) {
      const res = jqXHR.responseJSON;
      if (res) {
        showServerError(res.message || 'Terjadi kesalahan.');
        // Tampilkan error per-field dari server
        if (res.errors && typeof res.errors === 'object') {
          const fieldMap = {
            customer_name: 'customer-name',
            order_type:    'order-type',
            table_number:  'table-number',
            notes:         'notes',
            items:         'customer-name' // tampilkan di posisi umum
          };
          $.each(res.errors, function (field, msg) {
            const htmlField = fieldMap[field] || field;
            showFieldError(htmlField, msg);
          });
        }
      } else {
        showServerError('Gagal terhubung ke server. Periksa koneksi Anda.');
      }
      setSubmitting(false);
      isSubmitting = false;
    });
  });

  function setSubmitting(state) {
    $('#btn-submit-order').prop('disabled', state);
    $('#btn-submit-text').text(state ? 'Memproses...' : 'Buat pesanan');
    $('#btn-submit-spinner').toggleClass('hidden', !state);
  }

  function showServerError(msg) {
    $('#checkout-server-error-text').text(msg);
    $('#checkout-server-error').removeClass('hidden');
    $('html, body').animate({ scrollTop: 0 }, 300);
  }

  /* ============================================================
     LAYAR SUKSES (FR-CHK-04)
     ============================================================ */
  function onOrderSuccess(orderCode, total) {
    // Kosongkan keranjang
    CartStore.clear();

    // Tampilkan kode pesanan dan total
    $('#success-order-code').text(orderCode);
    $('#success-total').text(formatRupiah(total));

    // Tombol lacak pesanan
    const baseEl = document.querySelector('base');
    const base   = baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
    $('#btn-track-order').attr('href', base + 'track.php?code=' + encodeURIComponent(orderCode));

    // Ganti tampilan
    $('#checkout-form-section').addClass('hidden');
    $('#checkout-success-section').removeClass('hidden');

    // Scroll ke atas
    $('html, body').scrollTop(0);
  }

  /* ============================================================
     INISIALISASI
     ============================================================ */
  init();
});
