/**
 * Logika halaman kelola kategori admin (FR-CAT-01 s.d. FR-CAT-03, BR-14).
 */
(function ($) {
  'use strict';

  const $loading = $('#category-loading');
  const $error = $('#category-error');
  const $errorMessage = $('#category-error-message');
  const $empty = $('#category-empty');
  const $tableWrapper = $('#category-table-wrapper');
  const $tableBody = $('#category-table-body');

  const $modal = $('#modal-category-form');
  const $modalTitle = $('#modal-category-title');
  const $form = $('#form-category');
  const $inputId = $('#category-id');
  const $inputName = $('#category-name');
  const $inputSortOrder = $('#category-sort-order');
  const $errorName = $('#error-category-name');
  const $errorSortOrder = $('#error-category-sort-order');
  const $formAlert = $('#form-category-alert');
  const $formAlertMessage = $('#form-category-alert-message');
  const $btnSave = $('#btn-save-category');

  // Bersihkan pesan error pada form modal
  function clearFormErrors() {
    $formAlert.addClass('hidden');
    $formAlertMessage.text('');
    $errorName.addClass('hidden').text('');
    $errorSortOrder.addClass('hidden').text('');
    $inputName.removeClass('border-danger focus:border-danger bg-white');
    $inputSortOrder.removeClass('border-danger focus:border-danger bg-white');
  }

  function showFieldError($input, $errorEl, message) {
    $errorEl.text(message).removeClass('hidden');
    $input.addClass('border-danger focus:border-danger bg-white');
  }

  $inputName.on('input', function () {
    $errorName.addClass('hidden').text('');
    $inputName.removeClass('border-danger focus:border-danger bg-white');
    $formAlert.addClass('hidden');
  });

  $inputSortOrder.on('input', function () {
    $errorSortOrder.addClass('hidden').text('');
    $inputSortOrder.removeClass('border-danger focus:border-danger bg-white');
    $formAlert.addClass('hidden');
  });

  // Muat data kategori via AJAX (FR-CAT-01)
  function loadCategories() {
    $loading.removeClass('hidden');
    $error.addClass('hidden');
    $empty.addClass('hidden');
    $tableWrapper.addClass('hidden');

    Api.get('api/categories/list.php')
      .then(function (res) {
        $loading.addClass('hidden');
        const categories = res.data || [];

        if (!categories.length) {
          $empty.removeClass('hidden');
          return;
        }

        let rowsHtml = '';
        categories.forEach(function (cat) {
          const menuCount = parseInt(cat.menu_count, 10) || 0;
          const menuBadge = menuCount > 0
            ? `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-surface text-ink">${menuCount} menu</span>`
            : `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border border-line text-subtle">0 menu</span>`;

          rowsHtml += `
            <tr class="hover:bg-surface transition-colors">
              <td class="py-3.5 px-5">
                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-md bg-surface text-xs font-bold text-ink">
                  ${parseInt(cat.sort_order, 10) || 0}
                </span>
              </td>
              <td class="py-3.5 px-5">
                <span class="font-bold text-ink text-sm">${UI.escape(cat.name)}</span>
              </td>
              <td class="py-3.5 px-5 text-center">
                ${menuBadge}
              </td>
              <td class="py-3.5 px-5 text-right whitespace-nowrap">
                <button
                  type="button"
                  class="btn-edit h-8 px-3 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
                  data-id="${cat.id}"
                  data-name="${UI.escape(cat.name)}"
                  data-sort="${cat.sort_order}"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="btn-delete h-8 px-3 rounded-full bg-danger-light text-danger text-xs font-medium hover:bg-red-100 transition-colors cursor-pointer ml-1.5"
                  data-id="${cat.id}"
                  data-name="${UI.escape(cat.name)}"
                  data-count="${menuCount}"
                >
                  Hapus
                </button>
              </td>
            </tr>
          `;
        });

        $tableBody.html(rowsHtml);
        $tableWrapper.removeClass('hidden');
      })
      .catch(function (err) {
        $loading.addClass('hidden');
        $errorMessage.text(err.message || 'Gagal memuat daftar kategori.');
        $error.removeClass('hidden');
      });
  }

  // Tombol Coba Lagi saat gagal load
  $('#btn-retry-load').on('click', function () {
    loadCategories();
  });

  // Buka modal untuk Tambah Kategori
  function openAddModal() {
    clearFormErrors();
    $form[0].reset();
    $inputId.val('');
    $modalTitle.text('Tambah Kategori');
    $btnSave.text('Simpan Kategori');
    UI.openModal('modal-category-form');
    setTimeout(function () {
      $inputName.trigger('focus');
    }, 100);
  }

  $('#btn-add-category, .btn-trigger-add').on('click', function () {
    openAddModal();
  });

  // Buka modal untuk Edit Kategori
  $(document).on('click', '.btn-edit', function () {
    clearFormErrors();
    const id = $(this).data('id');
    const name = $(this).data('name');
    const sort = $(this).data('sort');

    $inputId.val(id);
    $inputName.val(name);
    $inputSortOrder.val(sort);

    $modalTitle.text('Edit Kategori');
    $btnSave.text('Perbarui Kategori');
    UI.openModal('modal-category-form');
    setTimeout(function () {
      $inputName.trigger('focus');
    }, 100);
  });

  // Submit form modal (Tambah / Edit via AJAX - FR-CAT-02)
  $form.on('submit', function (e) {
    e.preventDefault();
    clearFormErrors();

    const id = $.trim($inputId.val());
    const name = $.trim($inputName.val());
    const sortVal = $.trim($inputSortOrder.val());
    const sortOrder = sortVal === '' ? 0 : parseInt(sortVal, 10);

    let hasError = false;

    if (!name) {
      showFieldError($inputName, $errorName, 'Nama kategori wajib diisi.');
      hasError = true;
    } else if (name.length < 2) {
      showFieldError($inputName, $errorName, 'Nama kategori minimal 2 karakter.');
      hasError = true;
    } else if (name.length > 60) {
      showFieldError($inputName, $errorName, 'Nama kategori maksimal 60 karakter.');
      hasError = true;
    }

    if (isNaN(sortOrder) || sortOrder < 0) {
      showFieldError($inputSortOrder, $errorSortOrder, 'Urutan harus berupa bilangan bulat >= 0.');
      hasError = true;
    }

    if (hasError) {
      return;
    }

    UI.setLoading($btnSave, true);

    const isEdit = id !== '';
    const url = isEdit ? 'api/categories/update.php' : 'api/categories/create.php';
    const payload = isEdit
      ? { id: parseInt(id, 10), name: name, sort_order: sortOrder }
      : { name: name, sort_order: sortOrder };

    Api.post(url, payload)
      .then(function (res) {
        UI.setLoading($btnSave, false);
        UI.closeModal('modal-category-form');
        UI.toast('success', res.message || 'Kategori berhasil disimpan.');
        loadCategories();
      })
      .catch(function (err) {
        UI.setLoading($btnSave, false);

        if (err.errors) {
          if (err.errors.name) {
            showFieldError($inputName, $errorName, err.errors.name);
          }
          if (err.errors.sort_order) {
            showFieldError($inputSortOrder, $errorSortOrder, err.errors.sort_order);
          }
        }

        if (err.message && (!err.errors || !err.errors.name)) {
          $formAlertMessage.text(err.message);
          $formAlert.removeClass('hidden');
        }
      });
  });

  // Hapus Kategori dengan Konfirmasi (FR-CAT-03 & BR-14)
  $(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const count = parseInt($(this).data('count'), 10) || 0;

    let confirmMsg = `Apakah Anda yakin ingin menghapus kategori "${name}"?`;
    if (count > 0) {
      confirmMsg = `Kategori "${name}" saat ini masih memiliki ${count} menu terkait. Sesuai aturan sistem, kategori dengan menu tidak dapat dihapus. Tetap coba hapus?`;
    }

    UI.confirm(confirmMsg, 'Konfirmasi Hapus Kategori')
      .then(function (confirmed) {
        if (!confirmed) {
          return;
        }

        Api.post('api/categories/delete.php', { id: parseInt(id, 10) })
          .then(function (res) {
            UI.toast('success', res.message || 'Kategori berhasil dihapus.');
            loadCategories();
          })
          .catch(function (err) {
            UI.toast('error', err.message || 'Gagal menghapus kategori.');
          });
      });
  });

  // Panggilan awal saat halaman dimuat
  loadCategories();
})(jQuery);
