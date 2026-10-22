/**
 * Logika halaman login (public/assets/js/pages/login.js) — Gaya Uber Eats.
 */
(function ($) {
  'use strict';

  const $form = $('#form-login');
  if (!$form.length) {
    return;
  }

  const $btnLogin = $('#btn-login');
  const $username = $('#username');
  const $password = $('#password');
  const $errorUsername = $('#error-username');
  const $errorPassword = $('#error-password');
  const $alert = $('#login-alert');
  const $alertMessage = $('#login-alert-message');

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

  // Bersihkan error saat pengguna mengetik kembali
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
      password: passwordVal
    })
      .then(function (res) {
        UI.toast('success', res.message || 'Login berhasil.');

        // Alihkan halaman berdasarkan role / URL yang diberikan oleh server
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
          // Khusus rate limit / throttle, tampilkan banner alert
          $alertMessage.text(err.message);
          $alert.removeClass('hidden');
        }
      });
  });
})(jQuery);
