/**
 * payment.js — Modal Pembayaran Reusable
 * FR-PAY-01..04, prd.md §6.11, §12.4 "Pembayaran".
 *
 * Dipakai oleh:
 *  - cashier-orders.js  (bayar dari daftar pesanan masuk)
 *  - pos.js             (Simpan & Bayar dari POS)
 *
 * Cara pakai:
 *   PaymentModal.open(order, { onSuccess: function(result) { ... } });
 *
 * `order` harus berisi: { id, order_code, total, customer_name, order_type, table_number }
 */
window.PaymentModal = (function ($) {
  'use strict';

  /* ── Helpers ──────────────────────────────────────────────── */
  function getBaseUrl() {
    const baseEl = document.querySelector('base');
    return baseEl ? baseEl.href.replace(/\/+$/, '') + '/' : '/foodease/';
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  /* ── State ────────────────────────────────────────────────── */
  let currentOrder    = null;
  let currentMethod   = 'cash'; // 'cash' | 'qris'
  let onSuccessCallback = null;

  /* ── Inisialisasi (panggil sekali saat DOM siap) ─────────── */
  function init() {
    if ($('#payment-modal').length) return; // Sudah di-inject

    // Inject modal HTML ke body
    $('body').append(buildModalHtml());
    bindEvents();
  }

  /* ── Buka Modal ──────────────────────────────────────────── */
  function open(order, opts) {
    if (!order) return;
    init();

    currentOrder        = order;
    currentMethod       = 'cash';
    onSuccessCallback   = (opts && typeof opts.onSuccess === 'function') ? opts.onSuccess : null;

    // Isi header
    $('#pm-order-code').text(order.order_code || '—');
    const typeStr = order.order_type === 'dine_in'
      ? 'Makan di tempat' + (order.table_number ? ' · Meja ' + order.table_number : '')
      : 'Bawa pulang';
    $('#pm-order-meta').text((order.customer_name || '—') + ' · ' + typeStr);

    // Total
    const total = parseInt(order.total, 10) || 0;
    $('#pm-total-amount').text(window.Fmt.rupiah(total));
    $('#pm-qris-total').text(window.Fmt.rupiah(total));

    // Render tombol nominal cepat
    const quicks = buildQuickAmounts(total);
    const $quickContainer = $('#pm-quick-amounts').empty();
    quicks.forEach(function (q) {
      $('<button>')
        .attr('type', 'button')
        .addClass('pm-quick-amount h-9 px-3.5 rounded-lg bg-surface hover:bg-line text-xs font-semibold text-ink transition-colors cursor-pointer border border-line')
        .attr('data-amount', q.amount)
        .text(q.label)
        .appendTo($quickContainer);
    });

    // Reset ke mode Tunai
    setMethod('cash');

    // Reset input
    $('#pm-amount-input').val('');
    updateChange();

    // Sembunyikan QRIS, tampilkan tunai
    $('#pm-cash-section').removeClass('hidden');
    $('#pm-qris-section').addClass('hidden');
    $('#pm-btn-pay-cash').removeClass('hidden');
    $('#pm-btn-pay-qris').addClass('hidden');

    // Tampilkan modal
    $('#payment-modal').removeClass('hidden');
    setTimeout(function () {
      $('#pm-amount-input').trigger('focus');
    }, 100);
  }

  /* ── Tutup Modal ─────────────────────────────────────────── */
  function close() {
    $('#payment-modal').addClass('hidden');
    currentOrder = null;
    onSuccessCallback = null;
  }

  /* ── Set Metode ──────────────────────────────────────────── */
  function setMethod(method) {
    currentMethod = method;

    // Toggle segmented control
    if (method === 'cash') {
      $('#pm-tab-cash')
        .addClass('bg-white text-ink shadow-sm')
        .removeClass('text-subtle');
      $('#pm-tab-qris')
        .removeClass('bg-white text-ink shadow-sm')
        .addClass('text-subtle');
      $('#pm-cash-section').removeClass('hidden');
      $('#pm-qris-section').addClass('hidden');
      $('#pm-btn-pay-cash').removeClass('hidden');
      $('#pm-btn-pay-qris').addClass('hidden');
      // Update kembalian
      updateChange();
    } else {
      $('#pm-tab-qris')
        .addClass('bg-white text-ink shadow-sm')
        .removeClass('text-subtle');
      $('#pm-tab-cash')
        .removeClass('bg-white text-ink shadow-sm')
        .addClass('text-subtle');
      $('#pm-cash-section').addClass('hidden');
      $('#pm-qris-section').removeClass('hidden');
      $('#pm-btn-pay-cash').addClass('hidden');
      $('#pm-btn-pay-qris').removeClass('hidden');
    }
  }

  /* ── Update Kembalian Real-time ──────────────────────────── */
  function updateChange() {
    const total      = parseInt((currentOrder && currentOrder.total) || 0, 10);
    const rawInput   = $('#pm-amount-input').val().replace(/\D/g, '');
    const amountPaid = parseInt(rawInput, 10) || 0;
    const change     = amountPaid - total;

    const $changeRow  = $('#pm-change-row');
    const $changeVal  = $('#pm-change-value');
    const $shortfall  = $('#pm-shortfall');
    const $payBtn     = $('#pm-btn-pay-cash');

    if (amountPaid <= 0) {
      $changeRow.addClass('hidden');
      $shortfall.addClass('hidden');
      $payBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
      return;
    }

    if (amountPaid < total) {
      // Kurang bayar
      $changeRow.addClass('hidden');
      $shortfall
        .removeClass('hidden')
        .find('span')
        .text('Kurang ' + window.Fmt.rupiah(total - amountPaid));
      $payBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
    } else {
      // Lebih bayar atau pas
      $changeRow.removeClass('hidden');
      $changeVal.text(window.Fmt.rupiah(change));
      $shortfall.addClass('hidden');
      $payBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    }
  }

  /* ── Format input nominal (ribuan) ──────────────────────── */
  function formatAmountInput() {
    const $input = $('#pm-amount-input');
    const raw    = $input.val().replace(/\D/g, '');
    const num    = parseInt(raw, 10);
    if (!isNaN(num) && num > 0) {
      $input.val(num.toLocaleString('id-ID'));
    } else {
      $input.val('');
    }
  }

  /* ── Proses Pembayaran ───────────────────────────────────── */
  function processCashPayment() {
    if (!currentOrder) return;

    const total    = parseInt(currentOrder.total, 10);
    const rawInput = $('#pm-amount-input').val().replace(/\D/g, '');
    const amount   = parseInt(rawInput, 10) || 0;

    if (amount < total) {
      showToast('Nominal yang dibayarkan kurang dari total pesanan.', 'error');
      return;
    }

    submitPayment('cash', amount);
  }

  function processQrisPayment() {
    if (!currentOrder) return;
    submitPayment('qris', parseInt(currentOrder.total, 10));
  }

  function submitPayment(method, amountPaid) {
    const $btn = method === 'cash' ? $('#pm-btn-pay-cash') : $('#pm-btn-pay-qris');

    if (window.UI && typeof window.UI.setLoading === 'function') {
      window.UI.setLoading($btn, true);
    } else {
      $btn.prop('disabled', true).text('Memproses...');
    }

    $.ajax({
      url:         getBaseUrl() + 'api/payments/pay.php',
      type:        'POST',
      contentType: 'application/json',
      data:        JSON.stringify({
        order_id:    currentOrder.id,
        method:      method,
        amount_paid: amountPaid
      }),
      dataType:    'json',
      headers:     { 'X-CSRF-Token': getCsrfToken() }
    })
    .done(function (res) {
      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, false);
      } else {
        $btn.prop('disabled', false);
      }

      if (res && res.success && res.data) {
        close();
        showToast('Pembayaran berhasil! Kode: ' + res.data.payment_code, 'success');
        if (typeof onSuccessCallback === 'function') {
          onSuccessCallback(res.data);
        }
      } else {
        showToast(res.message || 'Gagal memproses pembayaran.', 'error');
      }
    })
    .fail(function (jqXHR) {
      if (window.UI && typeof window.UI.setLoading === 'function') {
        window.UI.setLoading($btn, false);
      } else {
        $btn.prop('disabled', false);
      }

      const res = jqXHR.responseJSON;
      showToast((res && res.message) ? res.message : 'Terjadi kesalahan saat memproses pembayaran.', 'error');
    });
  }

  /* ── Toast Notification ──────────────────────────────────── */
  function showToast(msg, type) {
    if (window.UI && typeof window.UI.toast === 'function') {
      window.UI.toast(type || 'info', msg);
      return;
    }
    // Fallback sederhana
    const bg = type === 'error' ? '#E11900' : '#000';
    const $t = $('<div>')
      .text(msg)
      .css({
        position: 'fixed', bottom: '24px', left: '50%', transform: 'translateX(-50%)',
        background: bg, color: '#fff', padding: '12px 20px',
        borderRadius: '9999px', fontSize: '13px', fontWeight: '500',
        zIndex: 9999, whiteSpace: 'nowrap', boxShadow: '0 4px 24px rgba(0,0,0,.18)'
      });
    $('body').append($t);
    setTimeout(function () { $t.fadeOut(300, function () { $(this).remove(); }); }, 3000);
  }

  /* ── Bind Events ─────────────────────────────────────────── */
  function bindEvents() {
    // Segmented control: Tunai / QRIS
    $(document).on('click', '#pm-tab-cash', function () { setMethod('cash'); });
    $(document).on('click', '#pm-tab-qris', function () { setMethod('qris'); });

    // Input nominal (format ribuan + update kembalian)
    $(document).on('input', '#pm-amount-input', function () {
      const raw = $(this).val().replace(/\D/g, '');
      const num = parseInt(raw, 10);
      // Ganti isi dengan angka tanpa format dulu (agar cursor tidak loncat)
      $(this).val(raw);
      updateChange();
    });

    $(document).on('blur', '#pm-amount-input', function () {
      formatAmountInput();
    });

    // Tombol nominal cepat
    $(document).on('click', '.pm-quick-amount', function () {
      const val = $(this).data('amount');
      $('#pm-amount-input').val(val);
      formatAmountInput();
      updateChange();
    });

    // Tombol Bayar (Tunai)
    $(document).on('click', '#pm-btn-pay-cash', function () {
      processCashPayment();
    });

    // Tombol Tandai Lunas (QRIS)
    $(document).on('click', '#pm-btn-pay-qris', function () {
      processQrisPayment();
    });

    // Tutup modal
    $(document).on('click', '#pm-overlay, #pm-btn-close', function () {
      close();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && currentOrder) {
        close();
      }
    });
  }

  /* ── Hitung tombol nominal cepat ──────────────────────────── */
  function buildQuickAmounts(total) {
    const buttons = [];
    // Selalu ada: uang pas
    buttons.push({ label: 'Uang pas', amount: total });
    // Tambahan nominal
    const extras = [50000, 100000, 200000];
    extras.forEach(function (v) {
      if (v > total) {
        // Pembulatan ke atas
        buttons.push({ label: window.Fmt.rupiah(v), amount: v });
      }
    });
    // Bulatkan ke 1000 terdekat di atas total
    const rounded = Math.ceil(total / 1000) * 1000;
    if (rounded !== total && !buttons.find(function (b) { return b.amount === rounded; })) {
      buttons.unshift({ label: window.Fmt.rupiah(rounded), amount: rounded });
    }
    return buttons;
  }

  /* ── Build HTML Modal ────────────────────────────────────── */
  function buildModalHtml() {
    return `
<div id="payment-modal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="pm-title">
  <!-- Overlay -->
  <div id="pm-overlay" class="fixed inset-0 bg-black/50"></div>

  <!-- Panel: Bottom sheet mobile, centered desktop -->
  <div class="fixed inset-x-0 bottom-0 sm:inset-0 flex items-end sm:items-center justify-center sm:p-4 pointer-events-none">
    <div class="pointer-events-auto w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl bg-white shadow-xl overflow-hidden max-h-[92vh] flex flex-col">

      <!-- Handle mobile -->
      <div class="w-10 h-1 bg-line rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>

      <!-- Header -->
      <div class="flex items-start justify-between px-6 py-4 border-b border-line shrink-0">
        <div>
          <h2 id="pm-title" class="text-base font-bold text-ink">Pembayaran</h2>
          <p class="text-xs text-subtle mt-0.5">
            <span id="pm-order-code" class="font-mono font-bold text-ink"></span>
            &nbsp;·&nbsp;
            <span id="pm-order-meta"></span>
          </p>
        </div>
        <button id="pm-btn-close" type="button" class="p-2 -mr-2 rounded-full hover:bg-surface text-subtle hover:text-ink transition-colors cursor-pointer" aria-label="Tutup">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Body (scrollable) -->
      <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

        <!-- Total Pesanan -->
        <div class="rounded-xl bg-surface border border-line px-5 py-4 flex items-center justify-between">
          <span class="text-sm text-subtle font-medium">Total Pesanan</span>
          <span id="pm-total-amount" class="text-2xl font-bold text-ink tracking-tight tabular-nums"></span>
        </div>

        <!-- Segmented control: Tunai / QRIS -->
        <div class="rounded-xl bg-surface p-1 flex gap-1">
          <button id="pm-tab-cash" type="button"
            class="flex-1 h-10 rounded-lg text-sm font-medium transition-all cursor-pointer bg-white text-ink shadow-sm">
            Tunai
          </button>
          <button id="pm-tab-qris" type="button"
            class="flex-1 h-10 rounded-lg text-sm font-medium transition-all cursor-pointer text-subtle">
            QRIS
          </button>
        </div>

        <!-- ── Seksi TUNAI ──────────────────────────────────────── -->
        <div id="pm-cash-section" class="space-y-4">

          <!-- Input Nominal -->
          <div>
            <label for="pm-amount-input" class="block text-xs font-medium text-subtle mb-1.5">Nominal Dibayarkan (Rp)</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-subtle text-sm font-medium pointer-events-none">Rp</span>
              <input
                type="text"
                id="pm-amount-input"
                inputmode="numeric"
                autocomplete="off"
                placeholder="0"
                class="h-14 w-full rounded-lg bg-surface pl-10 pr-4 text-2xl font-bold text-ink tabular-nums
                       border border-transparent focus:bg-white focus:border-ink focus:outline-none transition-colors"
              >
            </div>
          </div>

          <!-- Tombol Nominal Cepat -->
          <div id="pm-quick-amounts" class="flex flex-wrap gap-2">
            <!-- Diisi JS saat open() dipanggil -->
          </div>

          <!-- Kembalian -->
          <div id="pm-change-row" class="hidden flex items-center justify-between rounded-xl bg-accent-light border border-accent px-5 py-3">
            <span class="text-sm font-medium text-accent-dark">Kembalian</span>
            <span id="pm-change-value" class="text-xl font-bold text-accent-dark tabular-nums">Rp 0</span>
          </div>

          <!-- Kurang Bayar -->
          <div id="pm-shortfall" class="hidden flex items-center gap-2 rounded-xl bg-danger-light border border-danger px-5 py-3">
            <svg class="h-4 w-4 text-danger shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span class="text-sm font-medium text-danger"></span>
          </div>

        </div><!-- /pm-cash-section -->

        <!-- ── Seksi QRIS ───────────────────────────────────────── -->
        <div id="pm-qris-section" class="hidden space-y-4">

          <!-- QR Placeholder -->
          <div class="rounded-xl border-2 border-dashed border-line bg-surface/50 flex flex-col items-center justify-center py-10 gap-3">
            <!-- QR Code SVG Simulasi -->
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-ink">
              <!-- Sudut kiri atas -->
              <rect x="10" y="10" width="40" height="40" rx="4" fill="currentColor" opacity=".12"/>
              <rect x="18" y="18" width="24" height="24" rx="2" fill="currentColor" opacity=".25"/>
              <rect x="24" y="24" width="12" height="12" rx="1" fill="currentColor"/>
              <!-- Sudut kanan atas -->
              <rect x="70" y="10" width="40" height="40" rx="4" fill="currentColor" opacity=".12"/>
              <rect x="78" y="18" width="24" height="24" rx="2" fill="currentColor" opacity=".25"/>
              <rect x="84" y="24" width="12" height="12" rx="1" fill="currentColor"/>
              <!-- Sudut kiri bawah -->
              <rect x="10" y="70" width="40" height="40" rx="4" fill="currentColor" opacity=".12"/>
              <rect x="18" y="78" width="24" height="24" rx="2" fill="currentColor" opacity=".25"/>
              <rect x="24" y="84" width="12" height="12" rx="1" fill="currentColor"/>
              <!-- Data dots tengah (simulasi) -->
              <rect x="58" y="10" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="58" y="20" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="58" y="30" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="58" y="40" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="10" y="58" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="20" y="58" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="30" y="58" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="40" y="58" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="58" y="58" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="68" y="58" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="78" y="58" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="88" y="58" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="58" y="68" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="68" y="68" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="78" y="68" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="88" y="68" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="58" y="78" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="68" y="78" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="78" y="88" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="58" y="88" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="88" y="88" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="68" y="98" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="88" y="98" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="78" y="108" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="98" y="10" width="6" height="6" rx="1" fill="currentColor"/>
              <rect x="108" y="20" width="6" height="6" rx="1" fill="currentColor" opacity=".5"/>
              <rect x="98" y="30" width="6" height="6" rx="1" fill="currentColor"/>
            </svg>
            <p class="text-xs font-bold text-ink">Scan QR ini</p>
            <span class="inline-flex items-center gap-1 text-[11px] text-subtle bg-surface border border-line rounded-full px-3 py-1">
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              Simulasi — bukan QRIS sungguhan
            </span>
          </div>

          <!-- Total QRIS -->
          <div class="flex items-center justify-between px-1 text-sm">
            <span class="text-subtle">Total yang harus ditransfer</span>
            <span id="pm-qris-total" class="font-bold text-ink tabular-nums"></span>
          </div>

        </div><!-- /pm-qris-section -->

      </div><!-- /body -->

      <!-- Footer Aksi -->
      <div class="px-6 py-4 border-t border-line bg-white shrink-0 flex flex-col gap-2">
        <!-- Tombol Bayar (Tunai) -->
        <button
          id="pm-btn-pay-cash"
          type="button"
          class="h-12 w-full rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800
                 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Bayar
        </button>
        <!-- Tombol Tandai Lunas (QRIS) -->
        <button
          id="pm-btn-pay-qris"
          type="button"
          class="hidden h-12 w-full rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800
                 transition-colors cursor-pointer"
        >
          Tandai Lunas
        </button>
      </div>

    </div>
  </div>
</div>
`;
  }

  /* ── Public API ──────────────────────────────────────────── */
  return {
    open: function (order, opts) {
      init();

      currentOrder      = order;
      currentMethod     = 'cash';
      onSuccessCallback = (opts && typeof opts.onSuccess === 'function') ? opts.onSuccess : null;

      // Render tombol nominal cepat
      const total        = parseInt(order.total, 10) || 0;
      const quickAmounts = buildQuickAmounts(total);
      const $qa          = $('#pm-quick-amounts').empty();
      quickAmounts.forEach(function (btn) {
        $qa.append(
          $('<button type="button">')
            .addClass('pm-quick-amount h-9 px-3.5 rounded-full bg-surface border border-line text-xs font-medium text-ink hover:bg-neutral-200 transition-colors cursor-pointer tabular-nums')
            .text(btn.label)
            .attr('data-amount', btn.amount)
        );
      });

      open(order, opts);

      // Isi total QRIS
      $('#pm-qris-total').text(window.Fmt.rupiah(total));
    },

    close: close
  };
})(jQuery);
