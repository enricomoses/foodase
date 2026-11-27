<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Report;

/**
 * ReportService — logika bisnis laporan penjualan (FR-RPT-01..07).
 * Seluruh query SQL didelegasikan ke App\Models\Report, dan output HTTP ditangani Controller/Response.
 */
final class ReportService
{
    /**
     * @return array{total_revenue: int, transaction_count: int, average_per_transaction: int}
     */
    public static function summary(string $from, string $to): array
    {
        $row = Report::summary($from, $to);

        return [
            'total_revenue'           => (int) ($row['total_revenue'] ?? 0),
            'transaction_count'       => (int) ($row['transaction_count'] ?? 0),
            'average_per_transaction' => (int) ($row['average_per_transaction'] ?? 0),
        ];
    }

    /**
     * @return array<int, array{menu_name: string, total_qty: int, total_revenue: int}>
     */
    public static function bestSellers(string $from, string $to, int $limit = 10): array
    {
        $rows = Report::bestSellers($from, $to, $limit);

        return array_map(static fn(array $r): array => [
            'menu_name'     => (string) $r['menu_name'],
            'total_qty'     => (int) $r['total_qty'],
            'total_revenue' => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * @return array<int, array{date: string, transaction_count: int, total_revenue: int}>
     */
    public static function daily(string $from, string $to): array
    {
        $rows = Report::daily($from, $to);

        return array_map(static fn(array $r): array => [
            'date'              => (string) $r['date'],
            'transaction_count' => (int) $r['transaction_count'],
            'total_revenue'     => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * @return array<int, array{method: string, transaction_count: int, total_revenue: int}>
     */
    public static function byMethod(string $from, string $to): array
    {
        $rows = Report::byMethod($from, $to);

        return array_map(static fn(array $r): array => [
            'method'            => (string) $r['method'],
            'transaction_count' => (int) $r['transaction_count'],
            'total_revenue'     => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * @return array{unpaid_orders: int, processing_orders: int}
     */
    public static function orderCounts(string $from, string $to): array
    {
        $row = Report::orderCounts($from, $to);

        return [
            'unpaid_orders'     => (int) ($row['unpaid_orders'] ?? 0),
            'processing_orders' => (int) ($row['processing_orders'] ?? 0),
        ];
    }

    /**
     * Siapkan struktur data CSV laporan penjualan untuk dikirim oleh Response::csv().
     *
     * @return array{filename: string, headers: string[], rows: array<int, array<int, mixed>>, footer: array<int, array<int, mixed>>}
     */
    public static function buildCsvData(string $from, string $to): array
    {
        $transactions = Report::paidTransactions($from, $to);
        $grandTotal   = 0;
        $rows         = [];

        foreach ($transactions as $row) {
            $grandTotal += (int) $row['total'];
            $rows[] = [
                $row['payment_code'],
                $row['order_code'],
                $row['paid_at'],
                $row['customer_name'],
                $row['order_type'] === 'dine_in' ? 'Dine In' : 'Takeaway',
                $row['cashier_name'] ?? '-',
                $row['method'] === 'cash' ? 'Tunai' : 'QRIS',
                (int) $row['total'],
            ];
        }

        return [
            'filename' => sprintf('laporan-penjualan-%s-sd-%s.csv', $from, $to),
            'headers'  => [
                'Kode Pembayaran',
                'Kode Pesanan',
                'Waktu Pembayaran',
                'Nama Pelanggan',
                'Tipe Pesanan',
                'Kasir',
                'Metode Pembayaran',
                'Total (Rp)',
            ],
            'rows'     => $rows,
            'footer'   => [
                [],
                ['RINGKASAN'],
                ['Periode', $from . ' s.d. ' . $to],
                ['Jumlah Transaksi', count($rows)],
                ['Total Pendapatan', $grandTotal],
            ],
        ];
    }
}
