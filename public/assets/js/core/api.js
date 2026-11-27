/**
 * Wrapper pemanggilan API HTTP terstandarisasi berbasis Promise & jQuery AJAX.
 * Menangani base URL dan header X-CSRF-Token secara terpusat.
 */
window.Api = (function ($) {
  'use strict';

  function getBaseUrl() {
    var meta = document.querySelector('meta[name="app-base-url"]');
    if (meta && meta.content) {
      return meta.content.replace(/\/+$/, '') + '/';
    }
    var baseEl = document.querySelector('base');
    if (baseEl && baseEl.href) {
      return baseEl.href.replace(/\/+$/, '') + '/';
    }
    return '/';
  }

  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta.content || '') : '';
  }

  function normalizeResponse(res) {
    return {
      success: typeof res.success === 'boolean' ? res.success : true,
      message: res.message || '',
      data: res.data !== undefined ? res.data : null,
      errors: res.errors || []
    };
  }

  function normalizeError(jqXHR) {
    var message = 'Gagal terhubung ke server.';
    var errors = [];

    if (jqXHR.responseJSON) {
      if (jqXHR.responseJSON.message) {
        message = jqXHR.responseJSON.message;
      }
      if (jqXHR.responseJSON.errors) {
        errors = jqXHR.responseJSON.errors;
      }
    } else if (jqXHR.statusText && jqXHR.statusText !== 'error') {
      message = jqXHR.statusText;
    }

    return {
      success: false,
      message: message,
      data: null,
      errors: errors,
      status: jqXHR.status
    };
  }

  function resolveUrl(url) {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
      return url;
    }
    return getBaseUrl() + url.replace(/^\/+/, '');
  }

  return {
    baseUrl: getBaseUrl,
    csrfToken: getCsrfToken,
    resolveUrl: resolveUrl,

    get: function (url, params) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'GET',
          data: params || {},
          dataType: 'json'
        })
          .done(function (res) { resolve(normalizeResponse(res)); })
          .fail(function (jqXHR) { reject(normalizeError(jqXHR)); });
      });
    },

    post: function (url, data) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'POST',
          data: JSON.stringify(data || {}),
          contentType: 'application/json; charset=utf-8',
          headers: { 'X-CSRF-Token': getCsrfToken() },
          dataType: 'json'
        })
          .done(function (res) { resolve(normalizeResponse(res)); })
          .fail(function (jqXHR) { reject(normalizeError(jqXHR)); });
      });
    },

    postForm: function (url, formData) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: { 'X-CSRF-Token': getCsrfToken() },
          dataType: 'json'
        })
          .done(function (res) { resolve(normalizeResponse(res)); })
          .fail(function (jqXHR) { reject(normalizeError(jqXHR)); });
      });
    }
  };
})(jQuery);
