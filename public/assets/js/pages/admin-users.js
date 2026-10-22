/**
 * Script Halaman Admin — Kelola Akun Kasir (FR-USER-01..04, BR-15).
 * Standar: jQuery 3.7.1, Api wrapper (core/api.js), UI helpers (core/ui.js).
 */
$(function () {
  'use strict';

  // SVG Ikon untuk checklist
  const SVG_CHECK = '<svg class="h-3 w-3 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>';
  const SVG_DOT = '<span class="dot h-1.5 w-1.5 rounded-full bg-subtle"></span>';

  // State kasir lokal
  let cashiersList = [];

  // ============================================================
  // KOMPONEN PASSWORD: VALIDASI, REAL-TIME STRENGTH, CHECKLIST,
  // CAPS LOCK DETECTION, FLOATING LABEL, & TOGGLE SHOW/HIDE
  // ============================================================
  function setupPasswordComponent(options) {
    const $input = $(options.inputId);
    const $toggle = $(options.toggleBtn);
    const $caps = $(options.capsWarningId);
    const $label = $(options.strengthLabelId);
    const $bars = [
      $(options.barPrefix + '-1'),
      $(options.barPrefix + '-2'),
      $(options.barPrefix + '-3'),
      $(options.barPrefix + '-4'),
    ];
    const $reqs = {
      length: $(options.reqPrefix + '-length'),
      uppercase: $(options.reqPrefix + '-uppercase'),
      number: $(options.reqPrefix + '-number'),
      special: $(options.reqPrefix + '-special'),
    };
    const $submit = $(options.submitBtnId);

    // 1. Floating label class update
    function updateFloatingState() {
      const val = $input.val() || '';
      $input.toggleClass('has-value', val.length > 0);
    }

    $input.on('input change focus blur', updateFloatingState);

    // 2. Toggle tampil / sembunyikan password
    $toggle.on('click', function (e) {
      e.preventDefault();
      const currentType = $input.attr('type');
      const isPassword = currentType === 'password';
      $input.attr('type', isPassword ? 'text' : 'password');
      $toggle.find('.icon-eye').toggleClass('hidden', isPassword);
      $toggle.find('.icon-eye-off').toggleClass('hidden', !isPassword);
      $input.focus();
    });

    // 3. Peringatan Caps Lock is ON
    function checkCaps(e) {
      if (e && e.originalEvent && typeof e.originalEvent.getModifierState === 'function') {
        const isCapsOn = e.originalEvent.getModifierState('CapsLock');
        $caps.toggleClass('hidden', !isCapsOn);
      }
    }

    $input.on('keydown keyup', checkCaps);
    $input.on('focus', function (e) {
      checkCaps(e);
    });
    $input.on('blur', function () {
      $caps.addClass('hidden');
    });

    // 4. Checklist & Strength Meter Evaluator
    function evaluatePassword() {
      const pw = $input.val() || '';

      const criteria = {
        length: pw.length >= 8,
        uppercase: /[A-Z]/.test(pw),
        number: /[0-9]/.test(pw),
        special: /[^a-zA-Z0-9]/.test(pw),
      };

      // Perbarui checklist per item
      Object.keys(criteria).forEach(function (k) {
        const met = criteria[k];
        const $el = $reqs[k];
        const $iconBox = $el.find('.req-icon');

        if (met) {
          $el.removeClass('text-subtle').addClass('text-ink font-medium');
          $iconBox
            .removeClass('bg-line text-subtle')
            .addClass('bg-accent-light text-accent-dark')
            .html(SVG_CHECK);
        } else {
          $el.removeClass('text-ink font-medium').addClass('text-subtle');
          $iconBox
            .removeClass('bg-accent-light text-accent-dark')
            .addClass('bg-line text-subtle')
            .html(SVG_DOT);
        }
      });

      // Hitung total kriteria yang terpenuhi (0 s/d 4)
      const count =
        (criteria.length ? 1 : 0) +
        (criteria.uppercase ? 1 : 0) +
        (criteria.number ? 1 : 0) +
        (criteria.special ? 1 : 0);

      // Reset semua warna bar
      $bars.forEach(function ($bar) {
        $bar.removeClass('bg-danger bg-subtle bg-ink bg-accent').addClass('bg-line');
      });

      $label.removeClass('text-danger text-subtle text-ink text-accent font-bold font-semibold');

      if (pw.length === 0) {
        $label.text('-').addClass('text-subtle font-medium');
      } else if (count <= 1) {
        $label.text('Lemah').addClass('text-danger font-semibold');
        $bars[0].removeClass('bg-line').addClass('bg-danger');
      } else if (count === 2) {
        $label.text('Sedang').addClass('text-subtle font-semibold');
        $bars[0].removeClass('bg-line').addClass('bg-subtle');
        $bars[1].removeClass('bg-line').addClass('bg-subtle');
      } else if (count === 3) {
        $label.text('Kuat').addClass('text-ink font-semibold');
        $bars[0].removeClass('bg-line').addClass('bg-ink');
        $bars[1].removeClass('bg-line').addClass('bg-ink');
        $bars[2].removeClass('bg-line').addClass('bg-ink');
      } else if (count === 4) {
        $label.text('Sangat Kuat').addClass('text-accent font-bold');
        $bars[0].removeClass('bg-line').addClass('bg-accent');
        $bars[1].removeClass('bg-line').addClass('bg-accent');
        $bars[2].removeClass('bg-line').addClass('bg-accent');
        $bars[3].removeClass('bg-line').addClass('bg-accent');
      }

      // 6. Tombol simpan nonaktif sampai ke-4 persyaratan terpenuhi
      const allMet = count === 4;
      $submit.prop('disabled', !allMet);

      return allMet;
    }

    $input.on('input', evaluatePassword);

    // Reset component state
    function reset() {
      $input.val('').attr('type', 'password');
      $input.removeClass('has-value');
      $toggle.find('.icon-eye').removeClass('hidden');
      $toggle.find('.icon-eye-off').addClass('hidden');
      $caps.addClass('hidden');
      evaluatePassword();
    }

    return {
      evaluate: evaluatePassword,
      reset: reset,
    };
  }

  // Inisialisasi komponen password untuk form tambah dan form reset
  const createPwComp = setupPasswordComponent({
    inputId: '#create-password',
    toggleBtn: '#modal-create-user .btn-toggle-pw',
    capsWarningId: '#create-caps-warning',
    strengthLabelId: '#create-strength-label',
    barPrefix: '#create-bar',
    reqPrefix: '#create-req',
    submitBtnId: '#btn-submit-create',
  });

  const resetPwComp = setupPasswordComponent({
    inputId: '#reset-password-input',
    toggleBtn: '#modal-reset-password .btn-toggle-pw',
    capsWarningId: '#reset-caps-warning',
    strengthLabelId: '#reset-strength-label',
    barPrefix: '#reset-bar',
    reqPrefix: '#reset-req',
    submitBtnId: '#btn-submit-reset',
  });

  // ============================================================
  // FORMAT TANGGAL LOGIN TERAKHIR
  // ============================================================
  function formatLastLogin(dateStr) {
    if (!dateStr) {
      return '<span class="text-subtle italic">Belum pernah</span>';
    }
    try {
      const d = new Date(dateStr.replace(/-/g, '/'));
      if (isNaN(d.getTime())) {
        return UI.escape(dateStr);
      }
      return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch (e) {
      return UI.escape(dateStr);
    }
  }

  // ============================================================
  // LOAD & RENDER TABEL KASIR
  // ============================================================
  function loadCashiers() {
    $('#users-loading').removeClass('hidden');
    $('#users-error').addClass('hidden');
    $('#users-empty').addClass('hidden');
    $('#users-table-wrapper').addClass('hidden');

    Api.get('api/users/list.php')
      .then(function (res) {
        $('#users-loading').addClass('hidden');
        cashiersList = (res && res.data) ? res.data : [];
        renderTable();
      })
      .catch(function (err) {
        $('#users-loading').addClass('hidden');
        $('#users-error-message').text(err.message || 'Gagal memuat daftar kasir.');
        $('#users-error').removeClass('hidden');
      });
  }

  function renderTable() {
    if (!cashiersList.length) {
      $('#users-empty').removeClass('hidden');
      $('#users-table-wrapper').addClass('hidden');
      return;
    }

    const $tbody = $('#users-table-body');
    $tbody.empty();

    cashiersList.forEach(function (u) {
      const isActive = !!u.is_active;

      const badgeHtml = isActive
        ? '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-accent-light text-accent-dark">Aktif</span>'
        : '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-danger-light text-danger">Nonaktif</span>';

      const toggleChecked = isActive ? 'checked' : '';
      const toggleSwitch = `
        <label class="relative inline-flex items-center cursor-pointer ml-2" title="${isActive ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'}">
          <input type="checkbox" class="sr-only peer toggle-status" data-id="${u.id}" data-name="${UI.escape(u.name)}" ${toggleChecked}>
          <div class="w-9 h-5 bg-danger rounded-full peer peer-checked:bg-accent transition-colors"></div>
          <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
        </label>
      `;

      const rowHtml = `
        <tr class="hover:bg-surface/50 transition-colors" data-user-id="${u.id}">
          <td class="py-3 px-5">
            <div class="font-medium text-ink">${UI.escape(u.name)}</div>
          </td>
          <td class="py-3 px-5 font-mono text-xs text-subtle">
            @${UI.escape(u.username)}
          </td>
          <td class="py-3 px-5 text-center">
            <div class="inline-flex items-center justify-center">
              ${badgeHtml}
              ${toggleSwitch}
            </div>
          </td>
          <td class="py-3 px-5 text-xs text-subtle">
            ${formatLastLogin(u.last_login_at)}
          </td>
          <td class="py-3 px-5 text-right whitespace-nowrap">
            <button
              type="button"
              class="btn-edit-user h-8 px-3 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer"
              data-id="${u.id}"
            >
              Edit
            </button>
            <button
              type="button"
              class="btn-reset-pw h-8 px-3 rounded-full bg-surface text-ink text-xs font-medium hover:bg-neutral-200 transition-colors cursor-pointer ml-1.5"
              data-id="${u.id}"
            >
              Reset Password
            </button>
          </td>
        </tr>
      `;

      $tbody.append(rowHtml);
    });

    $('#users-table-wrapper').removeClass('hidden');
  }

  // Tombol Coba Lagi & Tambah Pertama
  $('#btn-retry-users').on('click', loadCashiers);
  $('#btn-empty-add-user, #btn-add-user').on('click', function () {
    openCreateModal();
  });

  // ============================================================
  // QUICK SWITCH AKTIF/NONAKTIF DI TABEL
  // ============================================================
  $('#users-table-body').on('change', '.toggle-status', function () {
    const $checkbox = $(this);
    const id = parseInt($checkbox.data('id'), 10);
    const name = $checkbox.data('name');
    const newActive = $checkbox.is(':checked');

    $checkbox.prop('disabled', true);

    Api.post('api/users/update.php', {
      id: id,
      name: name,
      is_active: newActive ? 1 : 0,
    })
      .then(function (res) {
        $checkbox.prop('disabled', false);
        UI.toast('success', res.message || 'Status kasir diperbarui.');
        // Update local item
        const item = cashiersList.find(function (x) { return x.id === id; });
        if (item) item.is_active = newActive;
        renderTable();
      })
      .catch(function (err) {
        $checkbox.prop('disabled', false);
        $checkbox.prop('checked', !newActive); // revert
        UI.toast('error', err.message || 'Gagal mengubah status kasir.');
      });
  });

  // ============================================================
  // MODAL TAMBAH KASIR
  // ============================================================
  function openCreateModal() {
    $('#form-create-user')[0].reset();
    $('#create-user-alert').addClass('hidden');
    $('#error-create-name').addClass('hidden').text('');
    $('#error-create-username').addClass('hidden').text('');
    $('#error-create-password').addClass('hidden').text('');
    createPwComp.reset();
    UI.openModal('modal-create-user');
    setTimeout(function () {
      $('#create-name').focus();
    }, 100);
  }

  $('#form-create-user').on('submit', function (e) {
    e.preventDefault();

    $('#create-user-alert').addClass('hidden');
    $('#error-create-name').addClass('hidden').text('');
    $('#error-create-username').addClass('hidden').text('');
    $('#error-create-password').addClass('hidden').text('');

    const name = $.trim($('#create-name').val());
    const username = $.trim($('#create-username').val());
    const password = $('#create-password').val();

    let hasClientError = false;

    if (!name || name.length < 2) {
      $('#error-create-name').text('Nama minimal 2 karakter.').removeClass('hidden');
      hasClientError = true;
    }

    if (!username || username.length < 4) {
      $('#error-create-username').text('Username minimal 4 karakter.').removeClass('hidden');
      hasClientError = true;
    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
      $('#error-create-username').text('Username hanya boleh berisi huruf, angka, dan underscore.').removeClass('hidden');
      hasClientError = true;
    }

    // Validasi 4 persyaratan password
    const isPwValid = createPwComp.evaluate();
    if (!isPwValid) {
      $('#error-create-password').text('Password harus memenuhi seluruh 4 persyaratan keamanan.').removeClass('hidden');
      hasClientError = true;
    }

    if (hasClientError) return;

    const $btn = $('#btn-submit-create');
    UI.setLoading($btn, true);

    Api.post('api/users/create.php', {
      name: name,
      username: username,
      password: password,
    })
      .then(function (res) {
        UI.setLoading($btn, false);
        UI.closeModal('modal-create-user');
        UI.toast('success', res.message || 'Kasir berhasil ditambahkan.');
        loadCashiers();
      })
      .catch(function (err) {
        UI.setLoading($btn, false);
        if (err.errors) {
          if (err.errors.name) $('#error-create-name').text(err.errors.name).removeClass('hidden');
          if (err.errors.username) $('#error-create-username').text(err.errors.username).removeClass('hidden');
          if (err.errors.password) $('#error-create-password').text(err.errors.password).removeClass('hidden');
        } else {
          $('#create-user-alert-message').text(err.message || 'Gagal menambahkan kasir.');
          $('#create-user-alert').removeClass('hidden');
        }
      });
  });

  // ============================================================
  // MODAL EDIT KASIR
  // ============================================================
  $('#users-table-body').on('click', '.btn-edit-user', function () {
    const id = parseInt($(this).data('id'), 10);
    const user = cashiersList.find(function (x) { return x.id === id; });
    if (!user) return;

    $('#edit-id').val(user.id);
    $('#edit-username').val(user.username);
    $('#edit-name').val(user.name);
    $('#edit-is-active').prop('checked', !!user.is_active);

    $('#edit-user-alert').addClass('hidden');
    $('#error-edit-name').addClass('hidden').text('');

    UI.openModal('modal-edit-user');
    setTimeout(function () {
      $('#edit-name').focus();
    }, 100);
  });

  $('#form-edit-user').on('submit', function (e) {
    e.preventDefault();

    $('#edit-user-alert').addClass('hidden');
    $('#error-edit-name').addClass('hidden').text('');

    const id = parseInt($('#edit-id').val(), 10);
    const name = $.trim($('#edit-name').val());
    const isActive = $('#edit-is-active').is(':checked') ? 1 : 0;

    if (!name || name.length < 2) {
      $('#error-edit-name').text('Nama minimal 2 karakter.').removeClass('hidden');
      return;
    }

    const $btn = $('#btn-submit-edit');
    UI.setLoading($btn, true);

    Api.post('api/users/update.php', {
      id: id,
      name: name,
      is_active: isActive,
    })
      .then(function (res) {
        UI.setLoading($btn, false);
        UI.closeModal('modal-edit-user');
        UI.toast('success', res.message || 'Data kasir diperbarui.');
        loadCashiers();
      })
      .catch(function (err) {
        UI.setLoading($btn, false);
        if (err.errors && err.errors.name) {
          $('#error-edit-name').text(err.errors.name).removeClass('hidden');
        } else {
          $('#edit-user-alert-message').text(err.message || 'Gagal memperbarui data kasir.');
          $('#edit-user-alert').removeClass('hidden');
        }
      });
  });

  // ============================================================
  // MODAL RESET PASSWORD
  // ============================================================
  $('#users-table-body').on('click', '.btn-reset-pw', function () {
    const id = parseInt($(this).data('id'), 10);
    const user = cashiersList.find(function (x) { return x.id === id; });
    if (!user) return;

    $('#reset-id').val(user.id);
    $('#reset-target-info').text('Kasir: ' + user.name + ' (@' + user.username + ')');

    $('#reset-user-alert').addClass('hidden');
    $('#error-reset-password').addClass('hidden').text('');
    resetPwComp.reset();

    UI.openModal('modal-reset-password');
    setTimeout(function () {
      $('#reset-password-input').focus();
    }, 100);
  });

  $('#form-reset-password').on('submit', function (e) {
    e.preventDefault();

    $('#reset-user-alert').addClass('hidden');
    $('#error-reset-password').addClass('hidden').text('');

    const id = parseInt($('#reset-id').val(), 10);
    const password = $('#reset-password-input').val();

    const isPwValid = resetPwComp.evaluate();
    if (!isPwValid) {
      $('#error-reset-password').text('Password baru harus memenuhi seluruh 4 persyaratan keamanan.').removeClass('hidden');
      return;
    }

    const $btn = $('#btn-submit-reset');
    UI.setLoading($btn, true);

    Api.post('api/users/reset-password.php', {
      id: id,
      password: password,
    })
      .then(function (res) {
        UI.setLoading($btn, false);
        UI.closeModal('modal-reset-password');
        UI.toast('success', res.message || 'Password kasir berhasil direset.');
      })
      .catch(function (err) {
        UI.setLoading($btn, false);
        if (err.errors && err.errors.password) {
          $('#error-reset-password').text(err.errors.password).removeClass('hidden');
        } else {
          $('#reset-user-alert-message').text(err.message || 'Gagal mereset password.');
          $('#reset-user-alert').removeClass('hidden');
        }
      });
  });

  // Inisialisasi awal: muat kasir
  loadCashiers();
});
