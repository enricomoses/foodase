/**
 * Logika halaman login & autentikasi (public/assets/js/pages/login.js) — Gaya Uber Eats.
 */
(function ($) {
  'use strict';

  // SVG Ikon untuk checklist validasi password
  const SVG_CHECK = '<svg class="h-3 w-3 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>';

  // Elemen Form Login (hanya ada di halaman login.php)
  const $form = $('#form-login');
  const $btnLogin = $('#btn-login');
  const $username = $('#username');
  const $password = $('#password');
  const $loginRole = $('#login-role');
  const $errorUsername = $('#error-username');
  const $errorPassword = $('#error-password');
  const $alert = $('#login-alert');
  const $alertMessage = $('#login-alert-message');

  // ============================================================
  // 1. TOGGLE SHOW/HIDE PASSWORD GLOBAL
  // ============================================================
  $(document).on('click', '.btn-toggle-pw', function (e) {
    e.preventDefault();
    const targetSelector = $(this).data('target');
    const $target = $(targetSelector);
    if (!$target.length) return;

    const currentType = $target.attr('type');
    const isPassword = currentType === 'password';
    $target.attr('type', isPassword ? 'text' : 'password');
    $(this).find('.icon-eye').toggleClass('hidden', isPassword);
    $(this).find('.icon-eye-off').toggleClass('hidden', !isPassword);
    $target.focus();
  });

  // ============================================================
  // 2. ROLE SELECTOR PILL PADA FORM LOGIN
  // ============================================================
  $('#role-pill-group').on('click', '.role-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const selectedRole = $btn.data('role') || '';

    $loginRole.val(selectedRole);

    $('#role-pill-group .role-btn')
      .removeClass('bg-black text-white shadow-sm')
      .addClass('text-subtle hover:text-ink');

    $btn
      .removeClass('text-subtle hover:text-ink')
      .addClass('bg-black text-white shadow-sm');
  });

  // ============================================================
  // 3. SUBMIT FORM LOGIN (JIKA DI HALAMAN LOGIN)
  // ============================================================
  if ($form.length) {
    function clearLoginErrors() {
      $alert.addClass('hidden');
      $alertMessage.text('');

      $errorUsername.addClass('hidden').text('');
      $errorPassword.addClass('hidden').text('');

      $username.removeClass('border-danger focus:border-danger bg-white');
      $password.removeClass('border-danger focus:border-danger bg-white');
    }

    function showFieldError($input, $errorEl, message) {
      $errorEl.text(message).removeClass('hidden');
      $input.addClass('border-danger focus:border-danger bg-white');
    }

    $username.on('input', function () {
      $errorUsername.addClass('hidden').text('');
      $username.removeClass('border-danger focus:border-danger bg-white');
      $alert.addClass('hidden');
    });

    $password.on('input', function () {
      $errorPassword.addClass('hidden').text('');
      $password.removeClass('border-danger focus:border-danger bg-white');
      $alert.addClass('hidden');
    });

    $form.on('submit', function (e) {
      e.preventDefault();
      clearLoginErrors();

      const usernameVal = $.trim($username.val());
      const passwordVal = $password.val();
      const roleVal = $.trim($loginRole.val());

      let hasClientError = false;

      if (!usernameVal) {
        showFieldError($username, $errorUsername, 'Username wajib diisi.');
        hasClientError = true;
      }

      if (!passwordVal) {
        showFieldError($password, $errorPassword, 'Password wajib diisi.');
        hasClientError = true;
      }

      if (hasClientError) {
        return;
      }

      UI.setLoading($btnLogin, true);

      const loginUrl = $form.attr('action') || 'api/auth/login.php';

      Api.post(loginUrl, {
        username: usernameVal,
        password: passwordVal,
        role: roleVal
      })
        .then(function (res) {
          UI.toast('success', res.message || 'Login berhasil.');

          const targetUrl = (res.data && res.data.redirect)
            ? res.data.redirect
            : (res.data && res.data.user && res.data.user.role === 'admin' ? 'admin/' : 'cashier/');

          setTimeout(function () {
            window.location.href = targetUrl;
          }, 300);
        })
        .catch(function (err) {
          UI.setLoading($btnLogin, false);

          let hasInlineField = false;

          if (err.errors) {
            if (err.errors.username) {
              showFieldError($username, $errorUsername, err.errors.username);
              hasInlineField = true;
            }
            if (err.errors.password) {
              showFieldError($password, $errorPassword, err.errors.password);
              hasInlineField = true;
            }
          }

          if (err.message && !hasInlineField) {
            $alertMessage.text(err.message);
            $alert.removeClass('hidden');
          } else if (err.message && err.status === 429) {
            $alertMessage.text(err.message);
            $alert.removeClass('hidden');
          }
        });
    });
  }

  // ============================================================
  // 4. HELPER REAL-TIME INTERAKTIF EVALUASI PASSWORD (WEAK=RED, STRONG=GREEN)
  // ============================================================
  function setupPasswordEvaluator(options) {
    const $input = $(options.inputId);
    if (!$input.length) return;

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

    function evaluate() {
      const pw = $input.val() || '';
      const criteria = {
        length: pw.length >= 8,
        uppercase: /[A-Z]/.test(pw),
        number: /[0-9]/.test(pw),
        special: /[^a-zA-Z0-9]/.test(pw),
      };

      let score = 0;
      Object.keys(criteria).forEach(function (k) {
        const met = criteria[k];
        const $el = $reqs[k];
        if (!$el.length) return;
        const $iconBox = $el.find('.req-icon');

        if (met) {
          score++;
          $el.removeClass('text-subtle').addClass('text-accent font-semibold');
          $iconBox
            .removeClass('bg-line text-subtle')
            .addClass('bg-accent-light text-accent')
            .html(SVG_CHECK);
        } else {
          $el.removeClass('text-accent font-semibold').addClass('text-subtle');
          $iconBox
            .removeClass('bg-accent-light text-accent')
            .addClass('bg-line text-subtle')
            .text('•');
        }
      });

      // Reset bars visual
      $bars.forEach(function ($bar) {
        $bar.removeClass('bg-danger bg-amber-500 bg-accent').addClass('bg-line');
      });

      // Reset input border dynamic color
      $input.removeClass('border-danger border-amber-500 border-accent focus:border-danger focus:border-amber-500 focus:border-accent');

      if (pw.length === 0) {
        $label.text('-').removeClass('text-danger text-amber-500 text-accent font-bold').addClass('text-subtle');
        return;
      }

      if (score <= 1) {
        // SANGAT LEMAH -> MERAH
        $label.text('Sangat Lemah').removeClass('text-subtle text-amber-500 text-accent').addClass('text-danger font-bold');
        $bars[0].removeClass('bg-line').addClass('bg-danger');
        $input.addClass('border-danger focus:border-danger');
      } else if (score === 2) {
        // CUKUP -> KUNING / AMBER
        $label.text('Cukup Kuat').removeClass('text-subtle text-danger text-accent').addClass('text-amber-500 font-bold');
        $bars[0].removeClass('bg-line').addClass('bg-amber-500');
        $bars[1].removeClass('bg-line').addClass('bg-amber-500');
        $input.addClass('border-amber-500 focus:border-amber-500');
      } else if (score === 3) {
        // KUAT -> HIJAU
        $label.text('Kuat').removeClass('text-subtle text-danger text-amber-500').addClass('text-accent font-bold');
        $bars[0].removeClass('bg-line').addClass('bg-accent');
        $bars[1].removeClass('bg-line').addClass('bg-accent');
        $bars[2].removeClass('bg-line').addClass('bg-accent');
        $input.addClass('border-accent focus:border-accent');
      } else {
        // SANGAT KUAT -> HIJAU PENUH
        $label.text('Sangat Kuat ✓').removeClass('text-subtle text-danger text-amber-500').addClass('text-accent font-bold');
        $bars.forEach(function ($b) {
          $b.removeClass('bg-line').addClass('bg-accent');
        });
        $input.addClass('border-accent focus:border-accent');
      }
    }

    $input.on('input keyup paste change', evaluate);
    evaluate();
  }

  // Inisialisasi evaluator untuk modal register & forgot
  setupPasswordEvaluator({
    inputId: '#reg-password',
    strengthLabelId: '#reg-strength-label',
    barPrefix: '#reg-bar',
    reqPrefix: '#reg-req',
  });

  setupPasswordEvaluator({
    inputId: '#page-reg-password',
    strengthLabelId: '#page-reg-strength-label',
    barPrefix: '#page-reg-bar',
    reqPrefix: '#page-reg-req',
  });

  setupPasswordEvaluator({
    inputId: '#forgot-password',
    strengthLabelId: '#forgot-strength-label',
    barPrefix: '#forgot-bar',
    reqPrefix: '#forgot-req',
  });

  // ============================================================
  // 5. TOMBOL KEMBALI KE LOGIN (BACK TO LOGIN)
  // ============================================================
  $(document).on('click', '.btn-back-to-login', function (e) {
    e.preventDefault();
    const $modalRegister = $('#modal-register');
    const $modalForgot = $('#modal-forgot');

    if ($modalRegister.length || $modalForgot.length) {
      $modalRegister.addClass('hidden');
      $modalForgot.addClass('hidden');
      $('body').removeClass('overflow-hidden');
    }

    if ($form.length) {
      $username.focus();
    } else {
      window.location.href = 'login.php';
    }
  });

  // ============================================================
  // 6. MODAL BUAT AKUN BARU (REGISTER MODAL DI LOGIN.PHP)
  // ============================================================
  const $modalRegister = $('#modal-register');
  const $formRegister = $('#form-register');
  const $btnSubmitRegister = $('#btn-submit-register');

  $('#btn-open-register').on('click', function (e) {
    e.preventDefault();
    $modalRegister.removeClass('hidden');
    $('body').addClass('overflow-hidden');
    $('#reg-name').focus();
  });

  // Role selector di dalam modal register
  $('#reg-role-group').on('click', '.reg-role-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const r = $btn.data('role') || 'cashier';
    $('#reg-role').val(r);

    $('#reg-role-group .reg-role-btn')
      .removeClass('bg-black text-white shadow-sm')
      .addClass('text-subtle hover:text-ink');

    $btn
      .removeClass('text-subtle hover:text-ink')
      .addClass('bg-black text-white shadow-sm');
  });

  if ($formRegister.length) {
    $formRegister.on('submit', function (e) {
      e.preventDefault();
      $('#register-alert').addClass('hidden');
      $('#error-reg-name, #error-reg-username, #error-reg-password').addClass('hidden').text('');

      const name = $.trim($('#reg-name').val());
      const username = $.trim($('#reg-username').val());
      const role = $.trim($('#reg-role').val()) || 'cashier';
      const password = $('#reg-password').val();

      let hasError = false;
      if (!name) {
        $('#error-reg-name').text('Nama lengkap wajib diisi.').removeClass('hidden');
        hasError = true;
      }
      if (!username) {
        $('#error-reg-username').text('Username wajib diisi.').removeClass('hidden');
        hasError = true;
      }
      if (!password) {
        $('#error-reg-password').text('Password wajib diisi.').removeClass('hidden');
        hasError = true;
      }

      if (hasError) return;

      UI.setLoading($btnSubmitRegister, true);

      Api.post('api/auth/register.php', {
        name: name,
        username: username,
        role: role,
        password: password
      })
        .then(function (res) {
          UI.setLoading($btnSubmitRegister, false);
          $modalRegister.addClass('hidden');
          $('body').removeClass('overflow-hidden');
          $formRegister[0].reset();

          UI.toast('success', res.message || 'Akun berhasil dibuat. Silakan login.');
          if ($username.length) {
            $username.val(username);
            $password.val('').focus();
          }
        })
        .catch(function (err) {
          UI.setLoading($btnSubmitRegister, false);
          if (err.errors) {
            if (err.errors.name) $('#error-reg-name').text(err.errors.name).removeClass('hidden');
            if (err.errors.username) $('#error-reg-username').text(err.errors.username).removeClass('hidden');
            if (err.errors.password) $('#error-reg-password').text(err.errors.password).removeClass('hidden');
          }
          if (err.message) {
            $('#register-alert-message').text(err.message);
            $('#register-alert').removeClass('hidden');
          }
        });
    });
  }

  // ============================================================
  // 7. FORM REGISTER DI HALAMAN STANDALONE (REGISTER.PHP)
  // ============================================================
  const $formRegisterPage = $('#form-register-page');
  const $btnPageSubmitRegister = $('#btn-page-submit-register');

  $('#page-reg-role-group').on('click', '.page-reg-role-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const r = $btn.data('role') || 'cashier';
    $('#page-reg-role').val(r);

    $('#page-reg-role-group .page-reg-role-btn')
      .removeClass('bg-black text-white shadow-sm')
      .addClass('text-subtle hover:text-ink');

    $btn
      .removeClass('text-subtle hover:text-ink')
      .addClass('bg-black text-white shadow-sm');
  });

  if ($formRegisterPage.length) {
    $formRegisterPage.on('submit', function (e) {
      e.preventDefault();
      $('#register-page-alert').addClass('hidden');
      $('#error-page-reg-name, #error-page-reg-username, #error-page-reg-password').addClass('hidden').text('');

      const name = $.trim($('#page-reg-name').val());
      const username = $.trim($('#page-reg-username').val());
      const role = $.trim($('#page-reg-role').val()) || 'cashier';
      const password = $('#page-reg-password').val();

      let hasError = false;
      if (!name) {
        $('#error-page-reg-name').text('Nama lengkap wajib diisi.').removeClass('hidden');
        hasError = true;
      }
      if (!username) {
        $('#error-page-reg-username').text('Username wajib diisi.').removeClass('hidden');
        hasError = true;
      }
      if (!password) {
        $('#error-page-reg-password').text('Password wajib diisi.').removeClass('hidden');
        hasError = true;
      }

      if (hasError) return;

      UI.setLoading($btnPageSubmitRegister, true);

      Api.post('api/auth/register.php', {
        name: name,
        username: username,
        role: role,
        password: password
      })
        .then(function (res) {
          UI.setLoading($btnPageSubmitRegister, false);
          UI.toast('success', res.message || 'Akun berhasil dibuat. Mengalihkan ke login...');
          setTimeout(function () {
            window.location.href = 'login.php';
          }, 1200);
        })
        .catch(function (err) {
          UI.setLoading($btnPageSubmitRegister, false);
          if (err.errors) {
            if (err.errors.name) $('#error-page-reg-name').text(err.errors.name).removeClass('hidden');
            if (err.errors.username) $('#error-page-reg-username').text(err.errors.username).removeClass('hidden');
            if (err.errors.password) $('#error-page-reg-password').text(err.errors.password).removeClass('hidden');
          }
          if (err.message) {
            $('#register-page-alert-message').text(err.message);
            $('#register-page-alert').removeClass('hidden');
          }
        });
    });
  }

  // ============================================================
  // 8. MODAL LUPA PASSWORD (RESET PASSWORD)
  // ============================================================
  const $modalForgot = $('#modal-forgot');
  const $formForgot = $('#form-forgot');
  const $btnSubmitForgot = $('#btn-submit-forgot');

  $('#btn-open-forgot').on('click', function (e) {
    e.preventDefault();
    $modalForgot.removeClass('hidden');
    $('body').addClass('overflow-hidden');

    const curU = $username.length ? $.trim($username.val()) : '';
    if (curU) {
      $('#forgot-username').val(curU);
      $('#forgot-password').focus();
    } else {
      $('#forgot-username').focus();
    }
  });

  if ($formForgot.length) {
    $formForgot.on('submit', function (e) {
      e.preventDefault();
      $('#forgot-alert').addClass('hidden');
      $('#error-forgot-username, #error-forgot-password').addClass('hidden').text('');

      const username = $.trim($('#forgot-username').val());
      const password = $('#forgot-password').val();

      let hasError = false;
      if (!username) {
        $('#error-forgot-username').text('Username wajib diisi.').removeClass('hidden');
        hasError = true;
      }
      if (!password) {
        $('#error-forgot-password').text('Password baru wajib diisi.').removeClass('hidden');
        hasError = true;
      }

      if (hasError) return;

      UI.setLoading($btnSubmitForgot, true);

      Api.post('api/auth/forgot-password.php', {
        username: username,
        password: password
      })
        .then(function (res) {
          UI.setLoading($btnSubmitForgot, false);
          $modalForgot.addClass('hidden');
          $('body').removeClass('overflow-hidden');
          $formForgot[0].reset();

          UI.toast('success', res.message || 'Password berhasil diperbarui.');
          if ($username.length) {
            $username.val(username);
            $password.val('').focus();
          }
        })
        .catch(function (err) {
          UI.setLoading($btnSubmitForgot, false);
          if (err.errors) {
            if (err.errors.username) $('#error-forgot-username').text(err.errors.username).removeClass('hidden');
            if (err.errors.password) $('#error-forgot-password').text(err.errors.password).removeClass('hidden');
          }
          if (err.message) {
            $('#forgot-alert-message').text(err.message);
            $('#forgot-alert').removeClass('hidden');
          }
        });
    });
  }

  // Modal dismiss handler
  $(document).on('click', '[data-modal-dismiss]', function () {
    if ($modalRegister.length) $modalRegister.addClass('hidden');
    if ($modalForgot.length) $modalForgot.addClass('hidden');
    $('body').removeClass('overflow-hidden');
  });

})(jQuery);
