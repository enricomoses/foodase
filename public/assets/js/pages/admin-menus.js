/**
 * Logika halaman kelola menu admin (FR-MENU-01..06, BR-02, BR-13).
 * Fitur: filter pencarian debounce 300ms, filter kategori, tabel paginasi,
 *        toggle ketersediaan, modal tambah/edit dengan preview foto, soft-delete.
 */
(function ($) {
  'use strict';

  /* ── Referensi DOM ─────────────────────────────────────────────── */
  const $loading      = $('#menu-loading');
  const $error        = $('#menu-error');
  const $errorMsg     = $('#menu-error-message');
  const $empty        = $('#menu-empty');
  const $tableWrapper = $('#menu-table-wrapper');
  const $tableBody    = $('#menu-table-body');
  const $pagination   = $('#menu-pagination');
  const $pageInfo     = $('#menu-pagination-info');
  const $pageBtns     = $('#menu-pagination-btns');
  const $search       = $('#menu-search');
  const $filterCat    = $('#menu-filter-category');

  const $modal        = $('#modal-menu-form');
  const $modalTitle   = $('#modal-menu-title');
  const $form         = $('#form-menu');
  const $inputId      = $('#menu-id');
  const $inputName    = $('#menu-name');
  const $inputCat     = $('#menu-category');
  const $inputPrice   = $('#menu-price');
  const $inputDesc    = $('#menu-description');
  const $inputAvail   = $('#menu-available');
  const $inputImage   = $('#menu-image');
  const $imgPreview   = $('#menu-img-preview');
  const $imgName      = $('#menu-image-name');
  const $btnSave      = $('#btn-save-menu');
  const $formAlert    = $('#form-menu-alert');
  const $formAlertMsg = $('#form-menu-alert-message');

  /* ── State ──────────────────────────────────────────────────────── */
  let currentPage      = 1;
  let searchTimeout    = null;
  const placeholderSrc = $imgPreview.attr('src');

  /* ── Helpers: errori field ──────────────────────────────────────── */
  function clearFormErrors() {
    $formAlert.addClass('hidden');
    $formAlertMsg.text('');
    ['name', 'category', 'price', 'description', 'image'].forEach(function (f) {
      $('#error-menu-' + f).addClass('hidden').text('');
    });
    [$inputName, $inputCat, $inputPrice, $inputDesc].forEach(function ($el) {
      $el.removeClass('border-danger focus:border-danger bg-white');
    });
  }

  function showFieldError($input, errorId, message) {
    $('#' + errorId).text(message).removeClass('hidden');
    if ($input) {
      $input.addClass('border-danger focus:border-danger bg-white');
    }
  }

  /* ── Format Rupiah ──────────────────────────────────────────────── */
  function formatRupiah(amount) {
    return 'Rp ' + parseInt(amount, 10).toLocaleString('id-ID');
  }

  /* ── Muat daftar menu ───────────────────────────────────────────── */
  function loadMenus(page) {
    page = page || 1;
    currentPage = page;

    $loading.removeClass('hidden');
    $error.addClass('hidden');
    $empty.addClass('hidden');
    $tableWrapper.addClass('hidden');
    $pagination.addClass('hidden');

    const params = {
      page: page,
      per_page: 15,
      include_unavailable: 1,
      q: $.trim($search.val()),
      category_id: $filterCat.val()
    };

    Api.get('api/menus/list.php', params)
      .then(function (res) {
        $loading.addClass('hidden');
        const data = res.data || {};
        const rows = data.data || [];

        if (!rows.length) {
          $empty.removeClass('hidden');
          return;
        }

        renderTable(rows);
        renderPagination(data);
        $tableWrapper.removeClass('hidden');
        $pagination.removeClass('hidden');
      })
      .catch(function (err) {
        $loading.addClass('hidden');
        $errorMsg.text(err.message || 'Gagal memuat daftar menu.');
        $error.removeClass('hidden');
      });
  }

  /* ── Render baris tabel ─────────────────────────────────────────── */
  function renderTable(rows) {
    let html = '';
    rows.forEach(function (m) {
      const isAvail = m.is_available;
      const imgSrc  = UI.escape(m.image_url || placeholderSrc);

      const availBadge = isAvail
        ? '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-accent-light text-accent-dark">Tersedia</span>'
        : '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-surface text-subtle border border-line">Tidak</span>';

      // Toggle switch
      const toggleChecked = isAvail ? 'checked' : '';
      const toggleSwitch = `
        <label class="relative inline-flex items-center cursor-pointer" title="${isAvail ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'}">
          <input type="checkbox" class="sr-only peer toggle-avail" data-id="${m.id}" ${toggleChecked}>
          <div class="w-9 h-5 bg-line rounded-full peer peer-checked:bg-black transition-colors"></div>
          <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
        </label>`;

      html += `
        <tr class="hover:bg-surface transition-colors" data-menu-id="${m.id}">
          <td class="py-3 px-4">
            <img
              src="${imgSrc}"
              alt="${UI.escape(m.name)}"
              class="w-12 h-12 rounded-lg object-cover bg-surface border border-line"
              onerror="this.src='${UI.escape(placeholderSrc)}'"
            >
          </td>
          <td class="py-3 px-4">
            <span class="font-medium text-ink">${UI.escape(m.name)}</span>
            ${m.description ? `<p class="text-xs text-subtle mt-0.5 line-clamp-1">${UI.escape(m.description)}</p>` : ''}
          </td>
          <td class="py-3 px-4">
            <span class="text-xs text-subtle">${UI.escape(m.category_name || '-')}</span>
          </td>
          <td class="py-3 px-4 text-right font-medium tabular-nums">
            ${formatRupiah(m.price)}
          </td>
          <td class="py-3 px-4 text-center">
            ${toggleSwitch}
          </td>
          <td class="py-3 px-4 text-right whitespace-nowrap">
            <button
              type="button"
              class="btn-edit-menu h-8 px-3 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
              data-id="${m.id}"
            >Edit</button>
            <button
              type="button"
              class="btn-delete-menu h-8 px-3 rounded-full bg-danger-light text-danger text-xs font-medium hover:bg-red-100 transition-colors cursor-pointer ml-1.5"
              data-id="${m.id}"
              data-name="${UI.escape(m.name)}"
            >Hapus</button>
          </td>
        </tr>`;
    });

    $tableBody.html(html);
  }

  /* ── Render paginasi ────────────────────────────────────────────── */
  function renderPagination(data) {
    const total   = data.total   || 0;
    const page    = data.page    || 1;
    const perPage = data.per_page || 15;
    const pages   = data.pages   || 1;

    const from = Math.min((page - 1) * perPage + 1, total);
    const to   = Math.min(page * perPage, total);
    $pageInfo.text(`Menampilkan ${from}–${to} dari ${total} menu`);

    let btnsHtml = '';

    // Tombol Prev
    const prevDisabled = page <= 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer';
    btnsHtml += `<button type="button" class="page-btn h-8 px-3 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors ${prevDisabled}" data-page="${page - 1}">← Prev</button>`;

    // Nomor halaman (tampil maks 5 di sekitar halaman aktif)
    const startPage = Math.max(1, page - 2);
    const endPage   = Math.min(pages, startPage + 4);

    for (let p = startPage; p <= endPage; p++) {
      const active = p === page ? 'bg-black text-white' : 'bg-surface text-ink hover:bg-neutral-200';
      btnsHtml += `<button type="button" class="page-btn h-8 w-8 rounded-full text-xs font-medium transition-colors ${active}" data-page="${p}">${p}</button>`;
    }

    // Tombol Next
    const nextDisabled = page >= pages ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer';
    btnsHtml += `<button type="button" class="page-btn h-8 px-3 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors ${nextDisabled}" data-page="${page + 1}">Next →</button>`;

    $pageBtns.html(btnsHtml);
  }

  /* ── Event: Paginasi ────────────────────────────────────────────── */
  $pageBtns.on('click', '.page-btn', function () {
    const page = parseInt($(this).data('page'), 10);
    if (page >= 1) {
      loadMenus(page);
    }
  });

  /* ── Event: Search dengan debounce 300ms ────────────────────────── */
  $search.on('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
      loadMenus(1);
    }, 300);
  });

  /* ── Event: Filter Kategori ─────────────────────────────────────── */
  $filterCat.on('change', function () {
    loadMenus(1);
  });

  /* ── Event: Retry ───────────────────────────────────────────────── */
  $('#btn-retry-menu').on('click', function () { loadMenus(currentPage); });

  /* ── Event: Buka modal tambah ───────────────────────────────────── */
  function openAddModal() {
    clearFormErrors();
    $form[0].reset();
    $inputId.val('');
    $inputAvail.prop('checked', true);
    $imgPreview.attr('src', placeholderSrc);
    $imgName.text('Belum ada foto dipilih');
    $modalTitle.text('Tambah Menu');
    $btnSave.text('Simpan Menu');
    UI.openModal('modal-menu-form');
    setTimeout(function () { $inputName.trigger('focus'); }, 100);
  }

  $('#btn-add-menu, #btn-empty-add').on('click', function () { openAddModal(); });

  /* ── Event: Buka modal edit ─────────────────────────────────────── */
  $(document).on('click', '.btn-edit-menu', function () {
    const id = $(this).data('id');

    clearFormErrors();
    $form[0].reset();
    $inputId.val(id);
    $modalTitle.text('Edit Menu');
    $btnSave.text('Perbarui Menu');

    // Reset preview foto
    $imgPreview.attr('src', placeholderSrc);
    $imgName.text('Memuat data...');

    UI.openModal('modal-menu-form');

    // Muat detail menu dari server
    Api.get('api/menus/detail.php', { id: id })
      .then(function (res) {
        const m = res.data;
        $inputName.val(m.name || '');
        $inputCat.val(m.category_id || '');
        $inputPrice.val(m.price || '');
        $inputDesc.val(m.description || '');
        $inputAvail.prop('checked', m.is_available === true || m.is_available === 1);

        // Preview foto
        if (m.image_url) {
          $imgPreview.attr('src', m.image_url);
          $imgName.text(m.image ? 'Foto terpasang — pilih untuk mengganti' : 'Belum ada foto');
        } else {
          $imgPreview.attr('src', placeholderSrc);
          $imgName.text('Belum ada foto dipilih');
        }
      })
      .catch(function () {
        UI.toast('error', 'Gagal memuat data menu.');
        UI.closeModal('modal-menu-form');
      });
  });

  /* ── Event: Preview foto baru ───────────────────────────────────── */
  $inputImage.on('change', function () {
    const file = this.files[0];
    if (!file) return;

    // Validasi client-side
    const maxSize = 2 * 1024 * 1024;
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowed.includes(file.type)) {
      showFieldError(null, 'error-menu-image', 'Hanya JPG, PNG, WebP yang diizinkan.');
      this.value = '';
      return;
    }

    if (file.size > maxSize) {
      showFieldError(null, 'error-menu-image', 'Ukuran foto tidak boleh melebihi 2 MB.');
      this.value = '';
      return;
    }

    $('#error-menu-image').addClass('hidden').text('');
    $imgName.text(file.name);

    const reader = new FileReader();
    reader.onload = function (e) {
      $imgPreview.attr('src', e.target.result);
    };
    reader.readAsDataURL(file);
  });

  /* ── Event: Submit form (Tambah / Edit) ─────────────────────────── */
  $form.on('submit', function (e) {
    e.preventDefault();
    clearFormErrors();

    const id    = $.trim($inputId.val());
    const name  = $.trim($inputName.val());
    const catId = $inputCat.val();
    const price = $.trim($inputPrice.val());
    const desc  = $.trim($inputDesc.val());
    let hasErr  = false;

    // Validasi client-side
    if (!name) {
      showFieldError($inputName, 'error-menu-name', 'Nama menu wajib diisi.');
      hasErr = true;
    } else if (name.length < 2) {
      showFieldError($inputName, 'error-menu-name', 'Nama menu minimal 2 karakter.');
      hasErr = true;
    } else if (name.length > 100) {
      showFieldError($inputName, 'error-menu-name', 'Nama menu maksimal 100 karakter.');
      hasErr = true;
    }

    if (!catId) {
      showFieldError($inputCat, 'error-menu-category', 'Kategori wajib dipilih.');
      hasErr = true;
    }

    const priceInt = parseInt(price, 10);
    if (!price) {
      showFieldError($inputPrice, 'error-menu-price', 'Harga wajib diisi.');
      hasErr = true;
    } else if (isNaN(priceInt) || priceInt < 1) {
      showFieldError($inputPrice, 'error-menu-price', 'Harga harus bilangan bulat minimal 1.');
      hasErr = true;
    } else if (priceInt > 10000000) {
      showFieldError($inputPrice, 'error-menu-price', 'Harga maksimal Rp 10.000.000.');
      hasErr = true;
    }

    if (desc.length > 500) {
      showFieldError($inputDesc, 'error-menu-description', 'Deskripsi maksimal 500 karakter.');
      hasErr = true;
    }

    if (hasErr) return;

    // Kirim sebagai FormData (multipart, mendukung file upload)
    const fd = new FormData($form[0]);

    // Nilai checkbox is_available tidak terkirim bila tidak dicentang
    if (!$inputAvail.is(':checked')) {
      fd.set('is_available', '0');
    } else {
      fd.set('is_available', '1');
    }

    const isEdit  = id !== '';
    const endpoint = isEdit ? 'api/menus/update.php' : 'api/menus/create.php';

    UI.setLoading($btnSave, true);

    Api.postForm(endpoint, fd)
      .then(function (res) {
        UI.setLoading($btnSave, false);
        UI.closeModal('modal-menu-form');
        UI.toast('success', res.message || 'Menu berhasil disimpan.');
        loadMenus(currentPage);
      })
      .catch(function (err) {
        UI.setLoading($btnSave, false);

        const errs = err.errors || {};
        if (errs.name)        showFieldError($inputName,  'error-menu-name',        errs.name);
        if (errs.category_id) showFieldError($inputCat,   'error-menu-category',    errs.category_id);
        if (errs.price)       showFieldError($inputPrice, 'error-menu-price',       errs.price);
        if (errs.description) showFieldError($inputDesc,  'error-menu-description', errs.description);
        if (errs.image)       showFieldError(null,        'error-menu-image',       errs.image);

        if (err.message && Object.keys(errs).length === 0) {
          $formAlertMsg.text(err.message);
          $formAlert.removeClass('hidden');
        }
      });
  });

  /* ── Event: Toggle ketersediaan langsung dari tabel ─────────────── */
  $(document).on('change', '.toggle-avail', function () {
    const $toggle = $(this);
    const menuId  = parseInt($toggle.data('id'), 10);
    // Simpan state sebelum toggle untuk rollback bila gagal
    const prevState = !$toggle.is(':checked');

    Api.post('api/menus/toggle.php', { id: menuId })
      .then(function (res) {
        const newState = res.data && res.data.is_available;
        $toggle.prop('checked', newState);
        UI.toast('success', res.message || 'Status menu diperbarui.');
      })
      .catch(function (err) {
        // Rollback state visual
        $toggle.prop('checked', prevState);
        UI.toast('error', err.message || 'Gagal mengubah status menu.');
      });
  });

  /* ── Event: Hapus menu (soft delete, BR-13) ─────────────────────── */
  $(document).on('click', '.btn-delete-menu', function () {
    const id   = $(this).data('id');
    const name = $(this).data('name');

    UI.confirm(`Hapus menu "${name}"? Tindakan ini tidak dapat dibatalkan.`, 'Konfirmasi Hapus Menu')
      .then(function (confirmed) {
        if (!confirmed) return;

        Api.post('api/menus/delete.php', { id: parseInt(id, 10) })
          .then(function (res) {
            UI.toast('success', res.message || 'Menu berhasil dihapus.');
            loadMenus(currentPage);
          })
          .catch(function (err) {
            UI.toast('error', err.message || 'Gagal menghapus menu.');
          });
      });
  });

  /* ── Muat data saat halaman pertama dibuka ──────────────────────── */
  loadMenus(1);

})(jQuery);
