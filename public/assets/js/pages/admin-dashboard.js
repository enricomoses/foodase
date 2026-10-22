/**
 * Script Halaman Dashboard Admin (FR-RPT-06).
 * Memakai endpoint reports/summary.php, reports/best-sellers.php, reports/daily.php.
 * Chart pendapatan 7 hari menggunakan Canvas API native (tanpa library tambahan).
 */
$(function () {
  'use strict';

  const cfg = window.DashboardConfig || {
    apiSummary:     '/foodease/public/api/reports/summary.php',
    apiBestSellers: '/foodease/public/api/reports/best-sellers.php',
    apiDaily:       '/foodease/public/api/reports/daily.php'
  };

  /* ── Helpers ─────────────────────────────────────────────────────────── */

  function formatDate(d) {
    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function fmtRupiah(amount) {
    if (window.Fmt && typeof window.Fmt.rupiah === 'function') {
      return window.Fmt.rupiah(amount);
    }
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
  }

  function fmtRupiahShort(amount) {
    const n = Number(amount || 0);
    if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1).replace('.', ',') + ' Jt';
    if (n >= 1000)    return 'Rp ' + Math.round(n / 1000) + ' Rb';
    return 'Rp ' + n;
  }

  function formatLongDate(d) {
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  }

  function formatShortDate(dateStr) {
    // "YYYY-MM-DD" → "DD/MM"
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length < 3) return dateStr;
    return parts[2] + '/' + parts[1];
  }

  /* ── Chart Revenue (Canvas Native) ───────────────────────────────────── */

  function isDarkMode() {
    return document.documentElement.classList.contains('dark');
  }

  function drawRevenueChart(canvas, labels, values) {
    const dpr    = window.devicePixelRatio || 1;
    const rect   = canvas.parentElement.getBoundingClientRect();
    const W      = rect.width  || canvas.parentElement.offsetWidth  || 600;
    const H      = rect.height || canvas.parentElement.offsetHeight || 200;

    canvas.width  = W * dpr;
    canvas.height = H * dpr;
    canvas.style.width  = W + 'px';
    canvas.style.height = H + 'px';

    const ctx  = canvas.getContext('2d');
    ctx.scale(dpr, dpr);

    const dark    = isDarkMode();
    const padTop  = 24;
    const padBot  = 36;
    const padLeft = 60;
    const padRight= 16;
    const chartW  = W - padLeft - padRight;
    const chartH  = H - padTop - padBot;

    // Warna sesuai tema
    const colGrid    = dark ? '#2a2a2a' : '#E2E2E2';
    const colLabel   = dark ? '#888888' : '#545454';
    const colBar     = dark ? '#ffffff' : '#000000';
    const colBarHov  = dark ? '#cccccc' : '#333333';
    const colValue   = dark ? '#cccccc' : '#545454';
    const colToday   = dark ? '#06C167' : '#048848';   // accent untuk hari ini (hari terakhir)

    const maxVal   = Math.max(...values, 1);
    const n        = labels.length;
    const barGap   = 8;
    const barW     = Math.max(12, (chartW - barGap * (n + 1)) / n);
    const barStep  = (chartW - barGap * (n + 1)) / n + barGap;

    ctx.clearRect(0, 0, W, H);

    // Grid lines (4 levels)
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
      const y = padTop + chartH - (chartH * i / 4);
      ctx.strokeStyle = colGrid;
      ctx.beginPath();
      ctx.moveTo(padLeft, y);
      ctx.lineTo(padLeft + chartW, y);
      ctx.stroke();

      // Label sumbu Y
      if (i > 0) {
        ctx.fillStyle = colLabel;
        ctx.font = '10px system-ui, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(fmtRupiahShort(maxVal * i / 4), padLeft - 6, y + 3);
      }
    }

    // Bar chart + label X
    values.forEach(function (val, idx) {
      const x      = padLeft + barGap + idx * barStep;
      const barH   = chartH * (val / maxVal);
      const y      = padTop + chartH - barH;
      const isLast = idx === values.length - 1;   // "hari ini" = batang terakhir

      // Bar
      ctx.fillStyle  = isLast ? colToday : colBar;
      ctx.beginPath();
      ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
      ctx.fill();

      // Label nilai di atas bar
      if (val > 0) {
        ctx.fillStyle  = isLast ? colToday : colValue;
        ctx.font       = '9px system-ui, sans-serif';
        ctx.textAlign  = 'center';
        ctx.fillText(fmtRupiahShort(val), x + barW / 2, y - 5);
      }

      // Label sumbu X (tanggal)
      ctx.fillStyle  = isLast ? colToday : colLabel;
      ctx.font       = isLast ? 'bold 10px system-ui, sans-serif' : '10px system-ui, sans-serif';
      ctx.textAlign  = 'center';
      ctx.fillText(formatShortDate(labels[idx]), x + barW / 2, padTop + chartH + 18);
    });
  }

  // Polyfill roundRect untuk browser lama
  if (!CanvasRenderingContext2D.prototype.roundRect) {
    CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) {
      if (typeof r === 'number') r = [r, r, r, r];
      this.beginPath();
      this.moveTo(x + r[0], y);
      this.lineTo(x + w - r[1], y);
      this.quadraticCurveTo(x + w, y, x + w, y + r[1]);
      this.lineTo(x + w, y + h - r[2]);
      this.quadraticCurveTo(x + w, y + h, x + w - r[2], y + h);
      this.lineTo(x + r[3], y + h);
      this.quadraticCurveTo(x, y + h, x, y + h - r[3]);
      this.lineTo(x, y + r[0]);
      this.quadraticCurveTo(x, y, x + r[0], y);
      this.closePath();
    };
  }

  /* ── Muat data dashboard ─────────────────────────────────────────────── */

  function loadDashboardData() {
    const now   = new Date();
    const today = formatDate(now);

    // Tanggal 6 hari lalu (7 hari termasuk hari ini)
    const d6ago = new Date(now);
    d6ago.setDate(d6ago.getDate() - 6);
    const fromDate = formatDate(d6ago);

    $('#dash-date-text').text(formatLongDate(now));
    $('#dash-error').addClass('hidden');
    setLoadingState(true);

    const paramsToday = { from: today, to: today };
    const params7days = { from: fromDate, to: today };

    // 1. Ringkasan performa hari ini + jumlah pesanan
    const pSummary = Api.get(cfg.apiSummary, paramsToday)
      .then(function (res) {
        const summary     = (res.data && res.data.summary) || {};
        const orderCounts = (res.data && res.data.order_counts) || {};

        const rev    = summary.total_revenue || 0;
        const count  = summary.transaction_count || 0;
        const unpaid = orderCounts.unpaid_orders || 0;
        const proc   = orderCounts.processing_orders || 0;

        $('#dash-today-revenue').text(fmtRupiah(rev));
        $('#dash-today-count').text(Number(count).toLocaleString('id-ID') + ' transaksi');
        $('#dash-today-unpaid').text(Number(unpaid).toLocaleString('id-ID') + ' pesanan');
        $('#dash-today-processing').text(Number(proc).toLocaleString('id-ID') + ' pesanan');
      });

    // 2. 5 Menu terlaris hari ini
    const pBest = Api.get(cfg.apiBestSellers, Object.assign({}, paramsToday, { limit: 5 }))
      .then(function (res) {
        renderTop5((res.data && res.data.items) || []);
      });

    // 3. Pendapatan 7 hari terakhir untuk chart
    const pDaily = Api.get(cfg.apiDaily, params7days)
      .then(function (res) {
        const rows  = (res.data && res.data.daily) || [];
        renderChart(rows, fromDate, today);
      });

    Promise.all([pSummary, pBest, pDaily])
      .catch(function (err) {
        var msg = (err && err.message) ? err.message : 'Gagal memuat data dashboard.';
        $('#dash-error-text').text(msg);
        $('#dash-error').removeClass('hidden');
      })
      .finally(function () {
        setLoadingState(false);
      });
  }

  /* ── Render Chart 7 Hari ─────────────────────────────────────────────── */

  function renderChart(rows, fromDate, toDate) {
    // Buat map tanggal → pendapatan
    const map = {};
    rows.forEach(function (r) {
      map[r.date] = Number(r.total_revenue || 0);
    });

    // Isi semua 7 hari (dengan 0 jika tidak ada data)
    const labels = [];
    const values = [];
    const cur    = new Date(fromDate + 'T00:00:00');
    const end    = new Date(toDate + 'T00:00:00');
    while (cur <= end) {
      const key = formatDate(cur);
      labels.push(key);
      values.push(map[key] || 0);
      cur.setDate(cur.getDate() + 1);
    }

    const total7 = values.reduce(function (a, b) { return a + b; }, 0);
    const hasData = values.some(function (v) { return v > 0; });

    $('#dash-chart-loading').addClass('hidden');

    if (!hasData) {
      $('#dash-chart-empty').removeClass('hidden');
      $('#dash-chart-total').addClass('hidden');
      return;
    }

    $('#dash-chart-empty').addClass('hidden');
    $('#dash-chart-wrap').removeClass('hidden');
    $('#dash-chart-total').removeClass('hidden');
    $('#dash-chart-total-val').text(fmtRupiah(total7));

    const canvas = document.getElementById('dash-revenue-chart');
    if (!canvas) return;

    // Simpan ke global agar bisa di-redraw saat tema berubah
    window._dashChartLabels = labels;
    window._dashChartValues = values;

    drawRevenueChart(canvas, labels, values);

    // Redraw saat resize
    let resizeTimer;
    $(window).off('resize.dashChart').on('resize.dashChart', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        drawRevenueChart(canvas, labels, values);
      }, 120);
    });
  }

  /* ── Render 5 Menu Terlaris ──────────────────────────────────────────── */

  function renderTop5(items) {
    const $tbody = $('#dash-bestsellers-tbody').empty();

    if (!items || items.length === 0) {
      $('#dash-bestsellers-empty').removeClass('hidden');
      $('#dash-bestsellers-content').addClass('hidden');
      return;
    }

    $('#dash-bestsellers-empty').addClass('hidden');
    $('#dash-bestsellers-content').removeClass('hidden');

    const maxQty = Math.max.apply(null, items.map(function (it) { return it.total_qty; }).concat([1]));

    items.forEach(function (item, index) {
      const percent = Math.min(Math.round((item.total_qty / maxQty) * 100), 100);

      const $tr = $('<tr>').addClass('hover:bg-surface transition-colors');

      // Rank
      $('<td>')
        .addClass('py-3 text-xs font-mono font-bold text-subtle w-8')
        .text(index + 1)
        .appendTo($tr);

      // Menu name + bar
      const $tdMenu = $('<td>').addClass('py-3 pr-4');
      $('<div>').addClass('text-xs font-medium text-ink').text(item.menu_name).appendTo($tdMenu);
      const $barWrap = $('<div>').addClass('h-1.5 w-full bg-surface rounded-full overflow-hidden mt-1.5');
      $('<div>')
        .addClass('h-full bg-black rounded-full transition-all duration-500')
        .css('width', percent + '%')
        .appendTo($barWrap);
      $barWrap.appendTo($tdMenu);
      $tdMenu.appendTo($tr);

      // Terjual
      $('<td>')
        .addClass('py-3 text-right text-xs font-medium text-ink tabular-nums whitespace-nowrap')
        .text(Number(item.total_qty).toLocaleString('id-ID') + ' porsi')
        .appendTo($tr);

      // Pendapatan
      $('<td>')
        .addClass('py-3 text-right text-xs font-bold text-ink tabular-nums whitespace-nowrap')
        .text(fmtRupiah(item.total_revenue))
        .appendTo($tr);

      $tbody.append($tr);
    });
  }

  /* ── Loading State ───────────────────────────────────────────────────── */

  function setLoadingState(isLoading) {
    if (isLoading) {
      $('#dash-bestsellers-loading').removeClass('hidden');
      $('#dash-bestsellers-content, #dash-bestsellers-empty').addClass('hidden');
      $('#dash-chart-loading').removeClass('hidden');
      $('#dash-chart-wrap, #dash-chart-empty').addClass('hidden');
    } else {
      $('#dash-bestsellers-loading').addClass('hidden');
      // chart skeleton diatur di renderChart()
    }
  }

  /* ── Events ──────────────────────────────────────────────────────────── */

  $('#btn-dash-retry').on('click', loadDashboardData);

  // Redraw chart saat tema berubah (dark ↔ light)
  document.addEventListener('themechange', function () {
    setTimeout(function () {
      const canvas = document.getElementById('dash-revenue-chart');
      if (canvas && !$('#dash-chart-wrap').hasClass('hidden')) {
        drawRevenueChart(canvas, window._dashChartLabels || [], window._dashChartValues || []);
      }
    }, 250);
  });

  /* ── Init ────────────────────────────────────────────────────────────── */
  loadDashboardData();
});
