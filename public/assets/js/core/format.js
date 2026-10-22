/**
 * Formatter data (Rupiah, Waktu, Badge Status Netral).
 */
window.Fmt = (function () {
  'use strict';

  const statusMap = {
    // Status Pesanan (Orders)
    pending: {
      label: 'Menunggu',
      cls: 'border-stone-300 bg-stone-100 text-stone-700'
    },
    processing: {
      label: 'Diproses',
      cls: 'border-stone-400 bg-stone-200 text-stone-800'
    },
    ready: {
      label: 'Siap Saji',
      cls: 'border-stone-400 bg-stone-200 text-stone-900 font-semibold'
    },
    completed: {
      label: 'Selesai',
      cls: 'border-stone-300 bg-stone-100 text-stone-600'
    },
    cancelled: {
      label: 'Dibatalkan',
      cls: 'border-stone-200 bg-stone-50 text-stone-400'
    },

    // Status Pembayaran (Payments)
    unpaid: {
      label: 'Belum Bayar',
      cls: 'border-stone-300 bg-stone-100 text-stone-700'
    },
    paid: {
      label: 'Lunas',
      cls: 'border-stone-400 bg-stone-200 text-stone-900 font-semibold'
    },
    void: {
      label: 'Batal / Void',
      cls: 'border-stone-200 bg-stone-50 text-stone-400'
    },

    // Ketersediaan Menu
    available: {
      label: 'Tersedia',
      cls: 'border-stone-300 bg-stone-100 text-stone-700'
    },
    unavailable: {
      label: 'Habis',
      cls: 'border-stone-200 bg-stone-50 text-stone-400'
    },

    // Tipe Pesanan
    dine_in: {
      label: 'Dine In',
      cls: 'border-stone-300 bg-stone-100 text-stone-700'
    },
    takeaway: {
      label: 'Takeaway',
      cls: 'border-stone-300 bg-stone-100 text-stone-700'
    }
  };

  return {
    /**
     * Format angka ke Rupiah (mis. 25000 -> "Rp 25.000").
     * @param {number|string} n
     * @returns {string}
     */
    rupiah: function (n) {
      const num = Math.round(Number(n) || 0);
      return 'Rp ' .concat(num.toLocaleString('id-ID'));
    },

    /**
     * Format tanggal dan jam ke standar Indonesia (mis. "22 Okt 2026, 13:00").
     * @param {string|Date} str
     * @returns {string}
     */
    datetime: function (str) {
      if (!str) return '-';
      const d = (str instanceof Date) ? str : new Date(str.replace(/-/g, '/'));
      if (isNaN(d.getTime())) {
        return str;
      }

      return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      }).format(d);
    },

    /**
     * Hasilkan markup HTML badge status netral sesuai prd.md §12.3.
     * @param {string} status
     * @returns {string}
     */
    statusBadge: function (status) {
      const key = String(status || '').toLowerCase();
      const meta = statusMap[key] || {
        label: status || '-',
        cls: 'border-stone-200 bg-stone-100 text-stone-600'
      };

      const escapedLabel = $('<div>').text(meta.label).html();
      return `<span class="inline-block rounded-md border px-2 py-0.5 text-xs ${meta.cls}">${escapedLabel}</span>`;
    }
  };
})();
