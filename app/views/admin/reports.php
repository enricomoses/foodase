<?php
declare(strict_types=1);

/**
 * View: Laporan Penjualan Admin (FR-RPT-01..07).
 * Layout: admin.php. Data dimuat via AJAX oleh admin-reports.js.
 */
?>
<div class="space-y-6">

  <!-- Filter Bar & Aksi Cepat -->
  <div class="rounded-xl border border-line bg-white p-5 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-base font-bold text-ink tracking-tight">Filter Periode</h2>
        <p class="text-xs text-subtle mt-0.5">Pilih rentang tanggal transaksi yang ingin dianalisis (maks. 366 hari).</p>
      </div>

      <!-- Tombol Cepat Periode -->
      <div class="flex flex-wrap gap-2">
        <button
          type="button"
          class="btn-quick-date h-8 px-3.5 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors"
          data-range="today"
        >
          Hari Ini
        </button>
        <button
          type="button"
          class="btn-quick-date h-8 px-3.5 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors"
          data-range="7days"
        >
          7 Hari Terakhir
        </button>
        <button
          type="button"
          class="btn-quick-date h-8 px-3.5 rounded-full bg-surface text-xs font-medium text-ink hover:bg-neutral-200 transition-colors"
          data-range="month"
        >
          Bulan Ini
        </button>
      </div>
    </div>

    <!-- Form Input Rentang Tanggal -->
    <div class="flex flex-wrap items-end gap-3 pt-1 border-t border-line">
      <!-- Dari Tanggal -->
      <div class="flex flex-col gap-1">
        <label for="report-from" class="text-xs font-medium text-subtle">Dari Tanggal</label>
        <input
          type="date"
          id="report-from"
          class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
        >
      </div>

      <!-- Sampai Tanggal -->
      <div class="flex flex-col gap-1">
        <label for="report-to" class="text-xs font-medium text-subtle">Sampai Tanggal</label>
        <input
          type="date"
          id="report-to"
          class="h-10 rounded-lg bg-surface border border-transparent px-3 text-sm focus:bg-white focus:border-black focus:outline-none"
        >
      </div>

      <!-- Tombol Terapkan -->
      <button
        id="btn-apply-report"
        type="button"
        class="h-10 px-5 rounded-full bg-black text-white text-sm font-medium hover:bg-neutral-800 transition-colors"
      >
        Terapkan
      </button>

      <!-- Tombol Ekspor CSV -->
      <button
        id="btn-export-csv"
        type="button"
        class="h-10 px-4 rounded-full bg-surface text-ink text-sm font-medium hover:bg-neutral-200 transition-colors flex items-center gap-2 ml-auto"
        title="Unduh laporan transaksi lunas dalam format CSV"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V3"/>
        </svg>
        <span>Ekspor CSV</span>
      </button>
    </div>
  </div>

  <!-- State Error Alert -->
  <div id="report-error" class="hidden rounded-xl border border-danger/30 bg-danger-light p-4 text-danger text-sm flex items-center justify-between">
    <span id="report-error-text">Gagal memuat data laporan.</span>
    <button id="btn-report-retry" class="h-8 px-3 rounded-full bg-danger text-white text-xs font-medium hover:bg-red-700">Coba Lagi</button>
  </div>

  <!-- 3 Kartu Ringkasan Penjualan (FR-RPT-02) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Kartu 1: Total Pendapatan -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Total Pendapatan</p>
      <div id="card-revenue" class="text-2xl lg:text-3xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-32 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-xs text-subtle">Hanya dari pembayaran lunas (paid)</p>
    </div>

    <!-- Kartu 2: Jumlah Transaksi -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Jumlah Transaksi</p>
      <div id="card-count" class="text-2xl lg:text-3xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-24 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-xs text-subtle">Transaksi berhasil diselesaikan</p>
    </div>

    <!-- Kartu 3: Rata-rata per Transaksi -->
    <div class="rounded-xl border border-line bg-white p-5 space-y-1">
      <p class="text-xs font-medium text-subtle uppercase tracking-wider">Rata-rata Transaksi</p>
      <div id="card-average" class="text-2xl lg:text-3xl font-bold tracking-tight text-ink tabular-nums">
        <span class="inline-block h-8 w-28 bg-surface rounded animate-pulse"></span>
      </div>
      <p class="text-xs text-subtle">Nilai rata-rata keranjang (basket size)</p>
    </div>
  </div>

  <!-- Baris Grid 2 Kolom: Menu Terlaris & Pendapatan per Metode -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Kolom Kiri (7 Kolom): 10 Menu Terlaris (FR-RPT-03) -->
    <div class="lg:col-span-7 rounded-xl border border-line bg-white p-5 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-ink tracking-tight">10 Menu Terlaris</h3>
          <p class="text-xs text-subtle">Berdasarkan kuantitas item dalam pesanan yang sudah dibayar</p>
        </div>
      </div>

      <!-- Loading Skeleton Menu Terlaris -->
      <div id="bestsellers-loading" class="space-y-3 py-2">
        <?php for ($i = 0; $i < 5; $i++): ?>
          <div class="h-9 rounded-lg bg-surface animate-pulse"></div>
        <?php endfor; ?>
      </div>

      <!-- State Kosong Menu Terlaris -->
      <div id="bestsellers-empty" class="hidden p-8 text-center text-sm text-subtle">
        Tidak ada data penjualan menu pada periode ini.
      </div>

      <!-- Tabel / List Menu Terlaris -->
      <div id="bestsellers-content" class="hidden overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-line text-xs text-subtle font-medium text-left">
              <th class="pb-2 w-8">#</th>
              <th class="pb-2">Menu</th>
              <th class="pb-2 text-right">Terjual</th>
              <th class="pb-2 text-right">Pendapatan</th>
            </tr>
          </thead>
          <tbody id="bestsellers-tbody" class="divide-y divide-line"></tbody>
        </table>
      </div>
    </div>

    <!-- Kolom Kanan (5 Kolom): Pendapatan per Metode Pembayaran (FR-RPT-04) -->
    <div class="lg:col-span-5 rounded-xl border border-line bg-white p-5 space-y-4 flex flex-col justify-between">
      <div class="space-y-4">
        <div>
          <h3 class="text-base font-bold text-ink tracking-tight">Metode Pembayaran</h3>
          <p class="text-xs text-subtle">Perbandingan kontribusi Tunai vs QRIS</p>
        </div>

        <!-- Loading Skeleton Metode -->
        <div id="methods-loading" class="space-y-3 py-2">
          <div class="h-16 rounded-xl bg-surface animate-pulse"></div>
          <div class="h-16 rounded-xl bg-surface animate-pulse"></div>
        </div>

        <!-- State Kosong Metode -->
        <div id="methods-empty" class="hidden p-8 text-center text-sm text-subtle">
          Belum ada transaksi pada periode ini.
        </div>

        <!-- Konten Metode Pembayaran -->
        <div id="methods-content" class="hidden space-y-3"></div>
      </div>

      <!-- Ringkasan Info Tambahan -->
      <div class="pt-4 border-t border-line text-xs text-subtle space-y-1">
        <p>• Transaksi yang dibatalkan / void tidak dihitung dalam pendapatan.</p>
        <p>• Data dihitung secara real-time dari database.</p>
      </div>
    </div>

  </div>

  <!-- Bagian Bawah: Tabel Pendapatan Harian (FR-RPT-05) -->
  <div class="rounded-xl border border-line bg-white p-5 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-base font-bold text-ink tracking-tight">Rincian Pendapatan Harian</h3>
        <p class="text-xs text-subtle">Perkembangan transaksi dan pendapatan per tanggal transaksi</p>
      </div>
    </div>

    <!-- Loading Skeleton Harian -->
    <div id="daily-loading" class="space-y-3 py-2">
      <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="h-9 rounded-lg bg-surface animate-pulse"></div>
      <?php endfor; ?>
    </div>

    <!-- State Kosong Harian -->
    <div id="daily-empty" class="hidden p-8 text-center text-sm text-subtle">
      Tidak ada transaksi pada periode tanggal ini.
    </div>

    <!-- Tabel Harian -->
    <div id="daily-table-wrap" class="hidden overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-line bg-surface text-xs text-subtle font-medium">
            <th class="h-10 px-4 text-left">Tanggal</th>
            <th class="h-10 px-4 text-right">Jumlah Transaksi</th>
            <th class="h-10 px-4 text-right">Total Pendapatan</th>
          </tr>
        </thead>
        <tbody id="daily-tbody"></tbody>
        <tfoot id="daily-tfoot" class="border-t-2 border-line bg-surface font-bold">
          <tr>
            <td class="h-11 px-4 text-xs font-medium text-subtle">Total Keseluruhan</td>
            <td id="daily-foot-count" class="h-11 px-4 text-right tabular-nums text-ink">0</td>
            <td id="daily-foot-total" class="h-11 px-4 text-right tabular-nums text-ink">Rp 0</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

</div>

<script>
  window.ReportConfig = {
    apiSummary:     '<?= e(url('api/reports/summary.php')) ?>',
    apiBestSellers: '<?= e(url('api/reports/best-sellers.php')) ?>',
    apiDaily:       '<?= e(url('api/reports/daily.php')) ?>',
    exportUrl:      '<?= e(url('api/reports/export.php')) ?>'
  };
</script>
