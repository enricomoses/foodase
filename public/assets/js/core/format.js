/**
 * Formatter data (Rupiah, Waktu, Badge Status gaya Uber Eats sesuai prd.md §12.3).
 */
window.Fmt = (function () {
  'use strict';

  const statusMap = {
    // Status Pesanan (Orders)
    pending: {
      label: 'Menunggu bayar',
      cls: 'bg-surface text-black'
    },
    processing: {
      label: 'Diproses',
      cls: 'bg-black text-white'
    },
    ready: {
      label: 'Siap',
      cls: 'bg-accent-light text-accent-dark'
    },
    completed: {
      label: 'Selesai',
      cls: 'border border-line text-subtle'
    },
    cancelled: {
      label: 'Dibatalkan',
      cls: 'bg-danger-light text-danger'
    },

    // Status Pembayaran (Payments)
    unpaid: {
      label: 'Belum bayar',
      cls: 'bg-surface text-black'
    },
    paid: {
      label: 'Lunas',
      cls: 'bg-accent-light text-accent-dark'
    },
    void: {
      label: 'Batal',
      cls: 'bg-danger-light text-danger'
    },

    // Ketersediaan Menu
    available: {
      label: 'Tersedia',
      cls: 'bg-accent-light text-accent-dark'
    },
    unavailable: {
      label: 'Habis',
      cls: 'bg-surface text-subtle'
    },

    // Tipe Pesanan
    dine_in: {
      label: 'Dine In',
      cls: 'bg-surface text-black'
    },
    takeaway: {
      label: 'Takeaway',
      cls: 'bg-surface text-black'
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
     * Hasilkan markup HTML badge status pil kecil sesuai prd.md §12.3.
     * @param {string} status
     * @returns {string}
     */
    statusBadge: function (status) {
      const key = String(status || '').toLowerCase();
      const meta = statusMap[key] || {
        label: status || '-',
        cls: 'border border-line text-subtle'
      };

      const escapedLabel = $('<div>').text(meta.label).html();
      return `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${meta.cls}">${escapedLabel}</span>`;
    }
  };
})();
