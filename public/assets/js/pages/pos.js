/**
 * pos.js — Skrip Halaman POS Kasir (Gaya Uber Eats).
 * FR-POS-01..03, prd.md §6.10, §4.3, §12.4 "Kasir – POS".
 *
 * State disimpan murni di memori halaman (bukan localStorage pelanggan).
 */
$(function () {
  'use strict';

  // Placeholder menu gambar default
  const defaultPlaceholder = 'assets/img/placeholder-menu.svg';

  /* ── State In-Memory POS ───────────────────────────────────── */
  let cartItems = [];           // Array of { id, name, price, image_url, qty }
  let currentOrderType = 'dine_in'; // 'dine_in' | 'takeaway'
  let allCategories = [];
  let allMenus = [];
  let currentCategoryId = '';
  let currentSearchQuery = '';
  let searchDebounceTimer = null;
  let isSubmitting = false;

  /* ── Helper Format Rupiah ──────────────────────────────────── */
  function formatRupiah(amount) {
    if (window.Fmt && typeof window.Fmt.rupiah === 'function') {
      return window.Fmt.rupiah(amount);
    }
    return 'Rp ' + (parseInt(amount, 10) || 0).toLocaleString('id-ID');
  }

  /* ── 1. Muat Kategori (Chips) ─────────────────────────────── */
  function loadCategories() {
    Api.get('api/categories/list.php')
      .then(function (res) {
        allCategories = (res && res.data) ? res.data : [];
        renderCategoryChips();
      })
      .catch(function () {
        // Fallback: gunakan hanya chip Semua
      });
  }

  function renderCategoryChips() {
    const $container = $('#pos-category-chips');
    $container.find('.pos-cat-chip:not([data-category-id=""])').remove();

    allCategories.forEach(function (cat) {
      const $chip = $('<button>')
        .attr('type', 'button')
        .attr('data-category-id', cat.id)
        .addClass('pos-cat-chip h-9 px-4 rounded-full text-xs font-semibold whitespace-nowrap transition-colors bg-surface text-subtle hover:bg-neutral-200 hover:text-ink cursor-pointer')
        .text(cat.name);

      $container.append($chip);
    });
  }

  // Klik chip kategori
  $('#pos-category-chips').on('click', '.pos-cat-chip', function () {
    const $btn = $(this);
    const catId = $btn.data('category-id');
    currentCategoryId = catId ? String(catId) : '';

    // Perbarui style tombol chip
    $('#pos-category-chips .pos-cat-chip')
      .removeClass('bg-black text-white')
      .addClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink');

    $btn
      .removeClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink')
      .addClass('bg-black text-white');

    loadMenus();
  });

  /* ── 2. Muat Menu ─────────────────────────────────────────── */
  function loadMenus() {
    $('#pos-menu-loading').removeClass('hidden');
    $('#pos-menu-error').addClass('hidden');
    $('#pos-menu-empty').addClass('hidden');
    $('#pos-menu-grid').addClass('hidden');

    const params = {
      include_unavailable: 1,
      per_page: 100
    };

    if (currentCategoryId !== '') {
      params.category_id = currentCategoryId;
    }

    if (currentSearchQuery !== '') {
      params.q = currentSearchQuery;
    }

    Api.get('api/menus/list.php', params)
      .then(function (res) {
        $('#pos-menu-loading').addClass('hidden');
        allMenus = (res && res.data && res.data.data) ? res.data.data : [];

        if (allMenus.length === 0) {
          if (currentSearchQuery !== '' || currentCategoryId !== '') {
            $('#pos-menu-empty-message').text('Tidak ada menu yang sesuai dengan filter atau pencarian Anda.');
          } else {
            $('#pos-menu-empty-message').text('Belum ada menu yang tersedia.');
          }
          $('#pos-menu-empty').removeClass('hidden');
        } else {
          renderMenuGrid();
          $('#pos-menu-grid').removeClass('hidden');
        }
      })
      .catch(function (err) {
        $('#pos-menu-loading').addClass('hidden');
        $('#pos-menu-error-message').text((err && err.message) || 'Gagal memuat katalog menu.');
        $('#pos-menu-error').removeClass('hidden');
      });
  }

  // Render Grid Menu
  function renderMenuGrid() {
    const $grid = $('#pos-menu-grid').empty();

    allMenus.forEach(function (menu) {
      const isAvailable = Boolean(menu.is_available);
      const cartItem = cartItems.find(function (it) { return it.id === menu.id; });
      const inCartQty = cartItem ? cartItem.qty : 0;
      const imageUrl = menu.image_url || defaultPlaceholder;

      const $card = $('<div>')
        .addClass('pos-menu-card border border-line rounded-xl p-3 bg-white transition-all flex justify-between gap-3 relative select-none')
        .attr('data-id', menu.id);

      if (isAvailable) {
        $card.addClass('cursor-pointer hover:border-ink hover:shadow-sm');
      } else {
        $card.addClass('opacity-50 cursor-not-allowed bg-surface/30');
      }

      // Kolom Teks Kiri
      const $left = $('<div>').addClass('flex-1 min-w-0 flex flex-col justify-between');
      const $info = $('<div>');
      const $name = $('<p>')
        .addClass('font-medium text-sm text-ink leading-snug line-clamp-2')
        .text(menu.name);

      if (menu.category_name) {
        const $cat = $('<span>')
          .addClass('text-[11px] text-subtle block mb-0.5')
          .text(menu.category_name);
        $info.append($cat);
      }
      $info.append($name);

      const $price = $('<div>')
        .addClass('font-bold text-sm text-ink mt-2 tabular-nums')
        .text(formatRupiah(menu.price));

      $left.append($info, $price);

      // Kolom Gambar Kanan
      const $right = $('<div>').addClass('relative w-20 h-20 rounded-lg overflow-hidden bg-surface shrink-0 border border-line/60');
      const $img = $('<img>')
        .attr('src', imageUrl)
        .attr('alt', menu.name)
        .addClass('w-full h-full object-cover')
        .on('error', function () {
          $(this).attr('src', defaultPlaceholder);
        });
      $right.append($img);

      if (isAvailable) {
        // Tombol Plus Bulat di pojok kanan bawah foto
        const $plusBtn = $('<div>')
          .addClass('absolute bottom-1 right-1 w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-ink')
          .html('<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>');
        $right.append($plusBtn);

        // Badge Jumlah di Keranjang (jika > 0)
        if (inCartQty > 0) {
          const $badge = $('<span>')
            .addClass('pos-item-qty-badge absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-black text-white text-[11px] font-bold shadow')
            .text(inCartQty + '×');
          $card.append($badge);
        }
      } else {
        // Label Habis
        const $habisBadge = $('<span>')
          .addClass('absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold bg-danger text-white shadow')
          .text('Habis');
        $card.append($habisBadge);
      }

      $card.append($left, $right);
      $grid.append($card);
    });
  }

  // Update badge jumlah pada kartu menu yang sudah dirender
  function updateMenuCardBadges() {
    $('#pos-menu-grid .pos-menu-card').each(function () {
      const $card = $(this);
      const menuId = parseInt($card.attr('data-id'), 10);
      const cartItem = cartItems.find(function (it) { return it.id === menuId; });
      const qty = cartItem ? cartItem.qty : 0;

      $card.find('.pos-item-qty-badge').remove();

      if (qty > 0 && !$card.hasClass('opacity-50')) {
        const $badge = $('<span>')
          .addClass('pos-item-qty-badge absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-black text-white text-[11px] font-bold shadow')
          .text(qty + '×');
        $card.append($badge);
      }
    });
  }

  // Klik kartu menu untuk menambah 1 item
  $('#pos-menu-grid').on('click', '.pos-menu-card', function (e) {
    e.preventDefault();
    const menuId = parseInt($(this).attr('data-id'), 10);
    const menu = allMenus.find(function (m) { return m.id === menuId; });

    if (!menu) return;

    if (!menu.is_available) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('warning', 'Menu "' + menu.name + '" sedang habis.');
      }
      return;
    }

    addToCart(menu);
  });

  /* ── 3. Pencarian Menu (Debounce 300 ms) ───────────────────── */
  $('#pos-search-input').on('input', function () {
    const val = $(this).val();
    if (val.length > 0) {
      $('#pos-search-clear').removeClass('hidden');
    } else {
      $('#pos-search-clear').addClass('hidden');
    }

    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(function () {
      currentSearchQuery = $.trim(val);
      loadMenus();
    }, 300);
  });

  // Hapus pencarian
  $('#pos-search-clear').on('click', function () {
    $('#pos-search-input').val('').trigger('focus');
    $(this).addClass('hidden');
    currentSearchQuery = '';
    loadMenus();
  });

  // Reset filter dari state kosong
  $('#pos-btn-reset-filter').on('click', function () {
    $('#pos-search-input').val('');
    $('#pos-search-clear').addClass('hidden');
    currentSearchQuery = '';
    currentCategoryId = '';

    $('#pos-category-chips .pos-cat-chip')
      .removeClass('bg-black text-white')
      .addClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink');

    $('#pos-category-chips .pos-cat-chip[data-category-id=""]')
      .removeClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink')
      .addClass('bg-black text-white');

    loadMenus();
  });

  // Tombol coba lagi jika error
  $('#pos-btn-retry-menu').on('click', function () {
    loadMenus();
  });

  /* ── 4. Logika Keranjang In-Memory POS ─────────────────────── */
  function addToCart(menu) {
    const existing = cartItems.find(function (it) { return it.id === menu.id; });
    if (existing) {
      if (existing.qty < 99) {
        existing.qty += 1;
      } else {
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('warning', 'Maksimal 99 porsi per item.');
        }
        return;
      }
    } else {
      cartItems.push({
        id: menu.id,
        name: menu.name,
        price: parseInt(menu.price, 10) || 0,
        image_url: menu.image_url || defaultPlaceholder,
        qty: 1
      });
    }

    renderCart();
    updateMenuCardBadges();
  }

  function updateItemQty(menuId, delta) {
    const index = cartItems.findIndex(function (it) { return it.id === menuId; });
    if (index === -1) return;

    cartItems[index].qty += delta;

    if (cartItems[index].qty <= 0) {
      cartItems.splice(index, 1);
    } else if (cartItems[index].qty > 99) {
      cartItems[index].qty = 99;
    }

    renderCart();
    updateMenuCardBadges();
  }

  function removeItem(menuId) {
    cartItems = cartItems.filter(function (it) { return it.id !== menuId; });
    renderCart();
    updateMenuCardBadges();
  }

  function clearCart() {
    cartItems = [];
    renderCart();
    updateMenuCardBadges();
  }

  function getCartTotal() {
    return cartItems.reduce(function (sum, item) {
      return sum + (item.price * item.qty);
    }, 0);
  }

  function getCartTotalCount() {
    return cartItems.reduce(function (sum, item) {
      return sum + item.qty;
    }, 0);
  }

  // Render Panel Keranjang
  function renderCart() {
    const $container = $('#pos-cart-items').empty();
    const count = getCartTotalCount();
    const total = getCartTotal();

    $('#pos-items-count-badge').text(count + ' item');
    $('#pos-total-amount').text(formatRupiah(total));

    if (cartItems.length === 0) {
      $('#pos-cart-empty').removeClass('hidden');
      $('#pos-btn-clear-cart').addClass('hidden');
      $('#pos-btn-save').prop('disabled', true);
      $('#pos-btn-save-pay').prop('disabled', true);
      return;
    }

    $('#pos-cart-empty').addClass('hidden');
    $('#pos-btn-clear-cart').removeClass('hidden');
    $('#pos-btn-save').prop('disabled', false);
    $('#pos-btn-save-pay').prop('disabled', false);

    cartItems.forEach(function (item) {
      const subtotal = item.price * item.qty;

      const $row = $('<div>')
        .addClass('pos-cart-row py-2.5 border-b border-line last:border-b-0 space-y-1.5')
        .attr('data-id', item.id);

      // Baris Atas: Nama & Tombol Hapus
      const $top = $('<div>').addClass('flex items-start justify-between gap-2');
      const $name = $('<span>')
        .addClass('text-xs font-medium text-ink flex-1 leading-snug line-clamp-2')
        .text(item.name);

      const $btnRemove = $('<button>')
        .attr('type', 'button')
        .attr('title', 'Hapus ' + item.name)
        .addClass('text-subtle hover:text-danger p-0.5 rounded transition-colors cursor-pointer shrink-0')
        .html('<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>')
        .on('click', function () {
          removeItem(item.id);
        });

      $top.append($name, $btnRemove);

      // Baris Bawah: Harga satuan, Stepper, Subtotal
      const $bottom = $('<div>').addClass('flex items-center justify-between text-xs');
      const $unitPrice = $('<span>')
        .addClass('text-[11px] text-subtle')
        .text('@ ' + formatRupiah(item.price));

      const $stepperAndSubtotal = $('<div>').addClass('flex items-center gap-3');

      // Stepper
      const $stepper = $('<div>').addClass('flex items-center gap-1.5 bg-surface px-1 py-0.5 rounded-full border border-line');

      const $btnMinus = $('<button>')
        .attr('type', 'button')
        .attr('title', 'Kurangi')
        .addClass('w-6 h-6 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 transition-all cursor-pointer')
        .html('<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg>')
        .on('click', function () {
          updateItemQty(item.id, -1);
        });

      const $qty = $('<span>')
        .addClass('w-5 text-center font-bold text-xs text-ink tabular-nums')
        .text(item.qty);

      const $btnPlus = $('<button>')
        .attr('type', 'button')
        .attr('title', 'Tambah')
        .addClass('w-6 h-6 rounded-full bg-white text-ink flex items-center justify-center shadow-sm hover:bg-neutral-100 transition-all cursor-pointer')
        .html('<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>')
        .on('click', function () {
          updateItemQty(item.id, 1);
        });

      $stepper.append($btnMinus, $qty, $btnPlus);

      const $subtotal = $('<span>')
        .addClass('font-bold text-xs text-ink tabular-nums')
        .text(formatRupiah(subtotal));

      $stepperAndSubtotal.append($stepper, $subtotal);
      $bottom.append($unitPrice, $stepperAndSubtotal);

      $row.append($top, $bottom);
      $container.append($row);
    });
  }

  // Tombol Kosongkan Keranjang
  $('#pos-btn-clear-cart').on('click', function () {
    if (cartItems.length === 0) return;
    clearCart();
  });

  /* ── 5. Toggle Tipe Pesanan (Dine In vs Takeaway) ─────────── */
  $('.pos-type-btn').on('click', function () {
    const type = $(this).data('type');
    currentOrderType = type;

    // Toggle tampilan segmented control
    if (type === 'dine_in') {
      $('#pos-type-dine-in')
        .addClass('bg-white text-ink shadow-sm')
        .removeClass('text-subtle');
      $('#pos-type-takeaway')
        .removeClass('bg-white text-ink shadow-sm')
        .addClass('text-subtle');
      $('#pos-table-group').removeClass('hidden');
    } else {
      $('#pos-type-takeaway')
        .addClass('bg-white text-ink shadow-sm')
        .removeClass('text-subtle');
      $('#pos-type-dine-in')
        .removeClass('bg-white text-ink shadow-sm')
        .addClass('text-subtle');
      $('#pos-table-group').addClass('hidden');
      $('#pos-error-table').addClass('hidden').text('');
    }
  });

  /* ── 6. Validasi & Pengiriman Pesanan ──────────────────────── */
  function clearFieldErrors() {
    $('#pos-error-name').addClass('hidden').text('');
    $('#pos-error-table').addClass('hidden').text('');
    $('#pos-error-notes').addClass('hidden').text('');
  }

  function validateForm() {
    clearFieldErrors();
    let isValid = true;

    // Cek item keranjang
    if (cartItems.length === 0) {
      if (window.UI && typeof window.UI.toast === 'function') {
        window.UI.toast('error', 'Keranjang kosong. Pilih minimal 1 menu.');
      }
      return false;
    }

    // Cek nama pelanggan
    const customerName = $.trim($('#pos-customer-name').val());
    if (customerName === '') {
      $('#pos-error-name').text('Nama pelanggan wajib diisi.').removeClass('hidden');
      $('#pos-customer-name').trigger('focus');
      isValid = false;
    } else if (customerName.length < 2) {
      $('#pos-error-name').text('Nama minimal 2 karakter.').removeClass('hidden');
      $('#pos-customer-name').trigger('focus');
      isValid = false;
    } else if (customerName.length > 50) {
      $('#pos-error-name').text('Nama maksimal 50 karakter.').removeClass('hidden');
      $('#pos-customer-name').trigger('focus');
      isValid = false;
    }

    // Cek nomor meja bila dine-in
    if (isValid && currentOrderType === 'dine_in') {
      const rawTable = $.trim($('#pos-table-number').val());
      const tableNum = parseInt(rawTable, 10);
      if (rawTable === '' || isNaN(tableNum)) {
        $('#pos-error-table').text('Nomor meja wajib diisi untuk makan di tempat.').removeClass('hidden');
        $('#pos-table-number').trigger('focus');
        isValid = false;
      } else if (tableNum < 1 || tableNum > 30) {
        $('#pos-error-table').text('Nomor meja harus antara 1 dan 30.').removeClass('hidden');
        $('#pos-table-number').trigger('focus');
        isValid = false;
      }
    }

    return isValid;
  }

  function resetPosForm() {
    cartItems = [];
    currentOrderType = 'dine_in';

    $('#pos-customer-name').val('');
    $('#pos-table-number').val('');
    $('#pos-notes').val('');

    // Kembalikan segmented control ke dine_in
    $('#pos-type-dine-in')
      .addClass('bg-white text-ink shadow-sm')
      .removeClass('text-subtle');
    $('#pos-type-takeaway')
      .removeClass('bg-white text-ink shadow-sm')
      .addClass('text-subtle');
    $('#pos-table-group').removeClass('hidden');

    clearFieldErrors();
    renderCart();
    updateMenuCardBadges();
  }

  function submitPosOrder(mode) {
    if (isSubmitting) return;

    if (!validateForm()) return;

    isSubmitting = true;
    const $targetBtn = (mode === 'save_and_pay') ? $('#pos-btn-save-pay') : $('#pos-btn-save');
    const originalBtnText = $targetBtn.html();

    if (window.UI && typeof window.UI.setLoading === 'function') {
      window.UI.setLoading($targetBtn, true);
    } else {
      $targetBtn.prop('disabled', true).text('Menyimpan...');
    }

    const customerName = $.trim($('#pos-customer-name').val());
    const tableNum = (currentOrderType === 'dine_in') ? parseInt($('#pos-table-number').val(), 10) : null;
    const notes = $.trim($('#pos-notes').val());

    const payload = {
      customer_name: customerName,
      order_type: currentOrderType,
      table_number: tableNum,
      notes: notes,
      items: cartItems.map(function (it) {
        return {
          menu_id: it.id,
          qty: it.qty
        };
      })
    };

    Api.post('api/orders/create-pos.php', payload)
      .then(function (res) {
        isSubmitting = false;
        if (window.UI && typeof window.UI.setLoading === 'function') {
          window.UI.setLoading($targetBtn, false);
        } else {
          $targetBtn.prop('disabled', false).html(originalBtnText);
        }

        const data = (res && res.data) ? res.data : null;
        if (!data) {
          if (window.UI && typeof window.UI.toast === 'function') {
            window.UI.toast('error', 'Gagal memproses pesanan.');
          }
          return;
        }

        // Simpan data order untuk mode Simpan & Bayar
        const orderData = {
          id: data.order_id,
          order_code: data.order_code,
          total: data.total,
          customer_name: data.customer_name,
          order_type: data.order_type,
          table_number: data.table_number
        };

        // Reset panel pesanan dan fokuskan pencarian
        resetPosForm();
        $('#pos-search-input').trigger('focus');

        if (mode === 'save') {
          if (window.UI && typeof window.UI.toast === 'function') {
            window.UI.toast('success', 'Pesanan ' + data.order_code + ' berhasil disimpan.');
          }
        } else if (mode === 'save_and_pay') {
          // Buka modal pembayaran dari payment.js
          if (window.PaymentModal && typeof window.PaymentModal.open === 'function') {
            window.PaymentModal.open(orderData, {
              onSuccess: function (payRes) {
                if (window.UI && typeof window.UI.toast === 'function') {
                  window.UI.toast('success', 'Pembayaran ' + data.order_code + ' lunas! Kode: ' + (payRes.payment_code || ''));
                }
              }
            });
          } else {
            if (window.UI && typeof window.UI.toast === 'function') {
              window.UI.toast('success', 'Pesanan ' + data.order_code + ' disimpan. Modal pembayaran tidak tersedia.');
            }
          }
        }
      })
      .catch(function (err) {
        isSubmitting = false;
        if (window.UI && typeof window.UI.setLoading === 'function') {
          window.UI.setLoading($targetBtn, false);
        } else {
          $targetBtn.prop('disabled', false).html(originalBtnText);
        }

        if (err && err.errors) {
          if (err.errors.customer_name) {
            $('#pos-error-name').text(err.errors.customer_name).removeClass('hidden');
          }
          if (err.errors.table_number) {
            $('#pos-error-table').text(err.errors.table_number).removeClass('hidden');
          }
          if (err.errors.notes) {
            $('#pos-error-notes').text(err.errors.notes).removeClass('hidden');
          }
        }

        const msg = (err && err.message) ? err.message : 'Terjadi kesalahan saat menyimpan pesanan.';
        if (window.UI && typeof window.UI.toast === 'function') {
          window.UI.toast('error', msg);
        }
      });
  }

  // Tombol Simpan
  $('#pos-btn-save').on('click', function () {
    submitPosOrder('save');
  });

  // Tombol Simpan & Bayar
  $('#pos-btn-save-pay').on('click', function () {
    submitPosOrder('save_and_pay');
  });

  /* ── 7. Inisialisasi Awal ─────────────────────────────────── */
  loadCategories();
  loadMenus();
  renderCart();

  // Fokus awal ke input pencarian
  setTimeout(function () {
    $('#pos-search-input').trigger('focus');
  }, 150);

});
