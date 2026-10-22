<?php
declare(strict_types=1);

/**
 * View Halaman Checkout Pelanggan.
 * FR-CHK-01..04, prd.md §6.7, §12.4.
 */
$maxTable = (int) ($max_table ?? 30);
$csrfToken = csrf_token();
?>

<!-- ============================================================
     HALAMAN: CHECKOUT (Kolom tunggal max-w-xl)
     ============================================================ -->
<div class="max-w-xl mx-auto" id="checkout-page" data-max-table="<?= e((string) $maxTable) ?>">

  <!-- ===== FORM CHECKOUT (ditampilkan awal) ===== -->
  <div id="checkout-form-section">
    <h1 class="text-2xl font-bold tracking-tight text-ink mb-6">Checkout</h1>

    <!-- Error global server -->
    <div id="checkout-server-error" class="hidden mb-4 flex items-start gap-2 rounded-xl border border-danger-light bg-danger-light/40 px-4 py-3">
      <svg class="h-4 w-4 text-danger shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p id="checkout-server-error-text" class="text-xs text-danger leading-relaxed"></p>
    </div>

    <!-- ── Kartu 1: Tipe Pesanan ── -->
    <div class="rounded-xl border border-line bg-white p-5 mb-4">
      <h2 class="text-base font-bold text-ink mb-3">Tipe pesanan</h2>

      <!-- Toggle pil dua pilihan (aktif: putih + bayangan di dalam wadah abu) -->
      <div class="flex p-1 rounded-full bg-surface gap-1" id="order-type-toggle" role="radiogroup" aria-label="Tipe pesanan">
        <button
          type="button"
          id="btn-type-dine"
          role="radio"
          aria-checked="true"
          data-value="dine_in"
          class="order-type-btn flex-1 h-10 rounded-full text-sm font-medium transition-all bg-white shadow-sm text-ink cursor-pointer"
        >
          Makan di tempat
        </button>
        <button
          type="button"
          id="btn-type-takeaway"
          role="radio"
          aria-checked="false"
          data-value="takeaway"
          class="order-type-btn flex-1 h-10 rounded-full text-sm font-medium transition-all text-subtle cursor-pointer hover:text-ink"
        >
          Bawa pulang
        </button>
      </div>

      <p id="error-order-type" class="hidden mt-1.5 text-xs text-danger"></p>
    </div>

    <!-- ── Kartu 2: Detail Pelanggan ── -->
    <div class="rounded-xl border border-line bg-white p-5 mb-4 space-y-4">
      <h2 class="text-base font-bold text-ink">Detail</h2>

      <!-- Nama Pelanggan -->
      <div>
        <label for="input-customer-name" class="block text-xs font-medium text-subtle mb-1.5">
          Nama <span class="text-danger">*</span>
        </label>
        <input
          type="text"
          id="input-customer-name"
          name="customer_name"
          maxlength="50"
          placeholder="Nama pemesan"
          autocomplete="name"
          class="h-12 w-full rounded-lg bg-surface px-4 border border-transparent focus:bg-white focus:border-ink focus:outline-none text-sm text-ink placeholder-subtle transition-colors"
        >
        <p id="error-customer-name" class="hidden mt-1 text-xs text-danger"></p>
      </div>

      <!-- Nomor Meja (tampil hanya dine-in) -->
      <div id="table-number-group">
        <label for="input-table-number" class="block text-xs font-medium text-subtle mb-1.5">
          Nomor meja <span class="text-danger">*</span>
        </label>
        <select
          id="input-table-number"
          name="table_number"
          class="h-12 w-full rounded-lg bg-surface px-4 border border-transparent focus:bg-white focus:border-ink focus:outline-none text-sm text-ink transition-colors appearance-none cursor-pointer"
        >
          <option value="">— Pilih nomor meja —</option>
          <?php for ($t = 1; $t <= $maxTable; $t++): ?>
          <option value="<?= e((string) $t) ?>">Meja <?= e((string) $t) ?></option>
          <?php endfor; ?>
        </select>
        <p id="error-table-number" class="hidden mt-1 text-xs text-danger"></p>
      </div>

      <!-- Catatan -->
      <div>
        <label for="input-notes" class="block text-xs font-medium text-subtle mb-1.5">
          Catatan <span class="text-subtle font-normal">(opsional)</span>
        </label>
        <textarea
          id="input-notes"
          name="notes"
          rows="2"
          maxlength="255"
          placeholder="Mis. kopi tanpa gula, pisahkan nasi..."
          class="w-full rounded-lg bg-surface px-4 py-3 border border-transparent focus:bg-white focus:border-ink focus:outline-none text-sm text-ink placeholder-subtle transition-colors resize-none"
        ></textarea>
        <p id="error-notes" class="hidden mt-1 text-xs text-danger"></p>
      </div>
    </div>

    <!-- ── Kartu 3: Ringkasan Pesanan ── -->
    <div class="rounded-xl border border-line bg-white p-5 mb-6">
      <h2 class="text-base font-bold text-ink mb-3">Pesanan Anda</h2>

      <!-- State kosong -->
      <div id="checkout-cart-empty" class="hidden py-4 text-center text-sm text-subtle">
        Keranjang kosong.
        <a href="<?= e(url('index.php')) ?>" class="font-medium text-ink hover:underline">Kembali ke menu</a>
      </div>

      <!-- State loading sinkron server -->
      <div id="checkout-cart-loading" class="space-y-3 py-2">
        <div class="h-4 w-3/4 rounded bg-surface animate-pulse"></div>
        <div class="h-4 w-1/2 rounded bg-surface animate-pulse"></div>
        <div class="h-4 w-2/3 rounded bg-surface animate-pulse"></div>
      </div>

      <!-- Daftar item -->
      <div id="checkout-cart-items" class="hidden space-y-2">
        <!-- Diisi oleh checkout.js -->
      </div>

      <!-- Total -->
      <div id="checkout-cart-total" class="hidden border-t border-line mt-3 pt-3 flex items-center justify-between">
        <span class="text-sm font-medium text-ink">Total</span>
        <span id="checkout-total-amount" class="text-base font-bold text-ink tabular-nums">Rp 0</span>
      </div>

      <!-- Banner item tidak tersedia -->
      <div id="checkout-unavailable-banner" class="hidden mt-3 flex items-start gap-2 rounded-xl border border-danger-light bg-danger-light/40 px-3 py-2">
        <p class="text-xs text-danger leading-relaxed">Beberapa item tidak lagi tersedia. Hapus dari keranjang sebelum melanjutkan.</p>
      </div>
    </div>

    <!-- ── Tombol Utama ── -->
    <button
      type="button"
      id="btn-submit-order"
      class="w-full h-12 rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center gap-2"
    >
      <span id="btn-submit-text">Buat pesanan</span>
      <!-- Spinner tersembunyi saat tidak loading -->
      <svg id="btn-submit-spinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
    </button>

    <p class="mt-3 text-center text-xs text-subtle">
      Dengan membuat pesanan, Anda setuju untuk membayar di kasir.
    </p>
  </div><!-- /checkout-form-section -->


  <!-- ===== LAYAR SUKSES (tersembunyi awal) ===== -->
  <div id="checkout-success-section" class="hidden flex flex-col items-center text-center py-8">

    <!-- Ikon Centang Besar -->
    <div class="w-20 h-20 rounded-full bg-accent-light flex items-center justify-center mb-6">
      <svg class="h-10 w-10 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    <h1 class="text-2xl font-bold tracking-tight text-ink mb-1">Pesanan dibuat!</h1>
    <p class="text-sm text-subtle mb-6">Tunjukkan kode ini kepada kasir dan lakukan pembayaran.</p>

    <!-- Kode Pesanan Besar -->
    <div class="w-full rounded-2xl border-2 border-ink bg-black text-white py-8 px-6 mb-6">
      <p class="text-xs font-medium text-white/60 mb-1 tracking-widest uppercase">Kode Pesanan</p>
      <p id="success-order-code" class="text-4xl sm:text-5xl font-bold tracking-tight tabular-nums">—</p>
    </div>

    <!-- Total -->
    <div class="w-full flex items-center justify-between rounded-xl border border-line bg-white px-5 py-3 mb-6">
      <span class="text-sm font-medium text-subtle">Total pembayaran</span>
      <span id="success-total" class="text-base font-bold text-ink tabular-nums">Rp 0</span>
    </div>

    <!-- Tombol Lacak & Kembali -->
    <a
      href="#"
      id="btn-track-order"
      class="w-full h-12 rounded-full bg-black text-white text-sm font-medium flex items-center justify-center hover:bg-neutral-800 transition-colors mb-3"
    >
      Lacak pesanan
    </a>
    <a
      href="<?= e(url('index.php')) ?>"
      class="w-full h-12 rounded-full bg-surface text-ink text-sm font-medium flex items-center justify-center hover:bg-neutral-200 transition-colors"
    >
      Kembali ke menu
    </a>
  </div><!-- /checkout-success-section -->

</div><!-- /checkout-page -->
