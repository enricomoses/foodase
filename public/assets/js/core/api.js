/**
 * Wrapper pemanggilan API HTTP terstandarisasi berbasis Promise & jQuery AJAX.
 */
window.Api = (function ($) {
  'use strict';

  function normalizeResponse(res) {
    return {
      success: typeof res.success === 'boolean' ? res.success : true,
      message: res.message || '',
      data: res.data !== undefined ? res.data : null,
      errors: res.errors || []
    };
  }

  function normalizeError(jqXHR) {
    let message = 'Gagal terhubung ke server.';
    let errors = [];

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
    const baseEl = document.querySelector('base');
    const base = baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
    return base + url.replace(/^\/+/, '');
  }

  return {
    /**
     * HTTP GET request.
     * @param {string} url
     * @param {object} [params={}]
     * @returns {Promise<{success: boolean, message: string, data: any, errors: any}>}
     */
    get: function (url, params) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'GET',
          data: params || {},
          dataType: 'json'
        })
        .done(function (res) {
          resolve(normalizeResponse(res));
        })
        .fail(function (jqXHR) {
          reject(normalizeError(jqXHR));
        });
      });
    },

    /**
     * HTTP POST request dengan payload JSON.
     * @param {string} url
     * @param {object} [data={}]
     * @returns {Promise<{success: boolean, message: string, data: any, errors: any}>}
     */
    post: function (url, data) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'POST',
          data: JSON.stringify(data || {}),
          contentType: 'application/json; charset=utf-8',
          dataType: 'json'
        })
        .done(function (res) {
          resolve(normalizeResponse(res));
        })
        .fail(function (jqXHR) {
          reject(normalizeError(jqXHR));
        });
      });
    },

    /**
     * HTTP POST request dengan FormData (mis. upload file atau form multipart).
     * @param {string} url
     * @param {FormData} formData
     * @returns {Promise<{success: boolean, message: string, data: any, errors: any}>}
     */
    postForm: function (url, formData) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: resolveUrl(url),
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(function (res) {
          resolve(normalizeResponse(res));
        })
        .fail(function (jqXHR) {
          reject(normalizeError(jqXHR));
        });
      });
    }
  };
})(jQuery);
