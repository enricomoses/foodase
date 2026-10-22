<?php
declare(strict_types=1);

/**
 * View: Dashboard Admin (FR-RPT-06).
 * Layout: admin.php. Data hari ini dimuat via AJAX oleh admin-dashboard.js.
 * Variabel: $name.
 */
?>
<div class="space-y-6">

  <!-- Header Sambutan -->
  <div class="rounded-xl border border-line bg-white p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h2 class="text-xl md:text-2xl font-bold tracking-tight text-ink">
        Halo, <?= e($name ?? 'Administrator') ?>!
      </h2>
      <p class="mt-1 text-sm text-subtle">
        Berikut ringkasan performa penjualan dan operasional kafe untuk hari ini, <span class="font-medium text-ink" id="dash-date-text"></span>.
      </p>
    </div>

    <!-- Tombol Pintasan Cepat Laporan -->
    <div class="flex items-center gap-2">
      <a
        href="<?= e(url('admin/reports.php')) ?>"
        class="h-10 px-5 rounded-full bg-black text-white text-xs font-medium hover:bg-neutral-800 transition-colors inline-flex items-center gap-2"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <span>Lihat Laporan Lengkap</span>
      </a>
    </div>
  </div>

  <!-- State Error Alert -->
  <div id="dash-error" class="hidden rounded-xl border border-danger/30 bg-danger-light p-4 text-danger text-sm flex items-center justify-between">
    <span id="dash-error-text">Gagal memuat data dashboard.</span>
    <button id="btn-dash-retry" class="h-8 px-3 rounded-full bg-danger text-white text-xs font-medium hover:bg-red-700">Coba Lagi</button>
  </div>

  <!-- 4 Kartu Ringkasan Hari Ini (FR-RPT-06) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- Kartu 1: Pendapatan Hari Ini -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Pendapatan Hari Ini</p>
      <div id="dash-today-revenue" class="text-2xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-28 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-[11px] text-subtle">Dari transaksi lunas</p>
    </div>

    <!-- Kartu 2: Transaksi Selesai/Lunas -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Transaksi Lunas</p>
      <div id="dash-today-count" class="text-2xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-20 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-[11px] text-subtle">Pembayaran berhasil hari ini</p>
    </div>

    <!-- Kartu 3: Pesanan Belum Dibayar -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Belum Dibayar</p>
      <div id="dash-today-unpaid" class="text-2xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-16 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-[11px] text-subtle">Menunggu pembayaran di kasir</p>
    </div>

    <!-- Kartu 4: Pesanan Sedang Diproses -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Sedang Diproses</p>
      <div id="dash-today-processing" class="text-2xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-16 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-[11px] text-subtle">Sedang disiapkan dapur</p>
    </div>

  </div>

  <!-- Baris Grid: 5 Menu Terlaris Hari Ini & Pintasan Operasional -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Kolom Kiri (7 Kolom): 5 Menu Terlaris Hari Ini -->
    <div class="lg:col-span-7 rounded-xl border border-line bg-white p-5 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-ink tracking-tight">5 Menu Terlaris Hari Ini</h3>
          <p class="text-xs text-subtle">Paling diminati pelanggan hari ini</p>
        </div>
      </div>

      <!-- Loading Skeleton 5 Menu -->
      <div id="dash-bestsellers-loading" class="space-y-3 py-2">
        <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="h-9 rounded-lg bg-surface animate-pulse"></div>
        <?php endfor; ?>
      </div>

      <!-- State Kosong -->
      <div id="dash-bestsellers-empty" class="hidden p-8 text-center text-sm text-subtle">
        Belum ada menu yang terjual hari ini.
      </div>

      <!-- Tabel 5 Menu -->
      <div id="dash-bestsellers-content" class="hidden overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-line text-xs text-subtle font-medium text-left">
              <th class="pb-2 w-8">#</th>
              <th class="pb-2">Menu</th>
              <th class="pb-2 text-right">Terjual</th>
              <th class="pb-2 text-right">Pendapatan</th>
            </tr>
          </thead>
          <tbody id="dash-bestsellers-tbody" class="divide-y divide-line"></tbody>
        </table>
      </div>
    </div>

    <!-- Kolom Kanan (5 Kolom): Pintasan Navigasi Cepat -->
    <div class="lg:col-span-5 rounded-xl border border-line bg-white p-5 space-y-4">
      <div>
        <h3 class="text-base font-bold text-ink tracking-tight">Aksi Cepat</h3>
        <p class="text-xs text-subtle">Akses langsung ke modul kafe</p>
      </div>

      <div class="grid grid-cols-1 gap-2.5">
        <!-- Pintasan POS -->
        <a
          href="<?= e(url('cashier/pos.php')) ?>"
          class="flex items-center justify-between p-3.5 rounded-xl border border-line bg-surface hover:bg-neutral-200 transition-colors group"
        >
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-white flex items-center justify-center text-ink shadow-sm">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-bold text-ink">Buka POS Kasir</p>
              <p class="text-[11px] text-subtle">Entri pesanan langsung di kasir</p>
            </div>
          </div>
          <svg class="h-4 w-4 text-subtle group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>

        <!-- Pintasan Pesanan Masuk -->
        <a
          href="<?= e(url('cashier/index.php')) ?>"
          class="flex items-center justify-between p-3.5 rounded-xl border border-line bg-surface hover:bg-neutral-200 transition-colors group"
        >
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-white flex items-center justify-center text-ink shadow-sm">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-bold text-ink">Antrean Pesanan Masuk</p>
              <p class="text-[11px] text-subtle">Pantau dan ubah status pesanan dapur</p>
            </div>
          </div>
          <svg class="h-4 w-4 text-subtle group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>

        <!-- Pintasan Kelola Menu -->
        <a
          href="<?= e(url('admin/menus.php')) ?>"
          class="flex items-center justify-between p-3.5 rounded-xl border border-line bg-surface hover:bg-neutral-200 transition-colors group"
        >
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-white flex items-center justify-center text-ink shadow-sm">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-bold text-ink">Kelola Menu & Ketersediaan</p>
              <p class="text-[11px] text-subtle">Tambah, perbarui harga, atau stok menu</p>
            </div>
          </div>
          <svg class="h-4 w-4 text-subtle group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>

        <!-- Pintasan Riwayat Pembayaran -->
        <a
          href="<?= e(url('cashier/history.php')) ?>"
          class="flex items-center justify-between p-3.5 rounded-xl border border-line bg-surface hover:bg-neutral-200 transition-colors group"
        >
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-white flex items-center justify-center text-ink shadow-sm">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-bold text-ink">Riwayat Transaksi & Struk</p>
              <p class="text-[11px] text-subtle">Cek struk atau lakukan void transaksi</p>
            </div>
          </div>
          <svg class="h-4 w-4 text-subtle group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>
      </div>
    </div>

  </div>

</div>

<script>
  window.DashboardConfig = {
    apiSummary:     '<?= e(url('api/reports/summary.php')) ?>',
    apiBestSellers: '<?= e(url('api/reports/best-sellers.php')) ?>'
  };
</script>
