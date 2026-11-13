/**
 * Logika halaman login Portal Staf & Administrator (admin/login.php) — Gaya Uber Eats.
 */
(function ($) {
  'use strict';

  const $form = $('#form-admin-login');
  const $btnLogin = $('#btn-admin-login');
  const $username = $('#admin-username');
  const $password = $('#admin-password');
  const $loginRole = $('#admin-login-role');
  const $errorUsername = $('#error-admin-username');
  const $errorPassword = $('#error-admin-password');
  const $alert = $('#admin-login-alert');
  const $alertMessage = $('#admin-login-alert-message');

  // 1. Toggle Password Visibility
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

  // 2. Role Selector Pill (Kasir / Administrator)
  $('#staff-role-pill-group').on('click', '.staff-role-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const selectedRole = $btn.data('role') || 'cashier';

    $loginRole.val(selectedRole);

    $('#staff-role-pill-group .staff-role-btn')
      .removeClass('bg-black text-white shadow-sm dark:bg-white dark:text-black')
      .addClass('text-subtle hover:text-ink');

    $btn
      .removeClass('text-subtle hover:text-ink')
      .addClass('bg-black text-white shadow-sm dark:bg-white dark:text-black');
  });

  // 3. Submit Form
  if ($form.length) {
    function clearErrors() {
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
      clearErrors();

      const usernameVal = $.trim($username.val());
      const passwordVal = $password.val();
      const roleVal = $.trim($loginRole.val()) || 'cashier';

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
        role: roleVal,
        scope: 'staff'
      })
        .then(function (res) {
          UI.toast('success', res.message || 'Login berhasil. Mengalihkan...');

          const targetUrl = (res.data && res.data.redirect)
            ? res.data.redirect
            : (res.data && res.data.user && res.data.user.role === 'admin' ? 'admin/' : 'cashier/');

          setTimeout(function () {
            window.location.href = targetUrl;
          }, 350);
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

})(jQuery);

