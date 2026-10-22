<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * ReportService — Layanan query agregat dan laporan penjualan (FR-RPT-01..07).
 * Aturan: Hanya menghitung pembayaran yang berstatus 'paid' (BR-12).
 */
final class ReportService
{
    /**
     * Ringkasan penjualan dalam rentang tanggal:
     * - total pendapatan (total_revenue)
     * - jumlah transaksi (transaction_count)
     * - rata-rata per transaksi (average_per_transaction)
     */
    public static function summary(string $from, string $to): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT 
                COALESCE(SUM(p.total), 0) AS total_revenue,
                COUNT(p.id) AS transaction_count,
                COALESCE(ROUND(AVG(p.total)), 0) AS average_per_transaction
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date'
        );

        $stmt->execute([
            ':from_date' => $from . ' 00:00:00',
            ':to_date'   => $to . ' 23:59:59',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_revenue'           => (int) ($row['total_revenue'] ?? 0),
            'transaction_count'       => (int) ($row['transaction_count'] ?? 0),
            'average_per_transaction' => (int) ($row['average_per_transaction'] ?? 0),
        ];
    }

    /**
     * Menu terlaris dalam rentang tanggal berdasarkan pesanan yang lunas (FR-RPT-03).
     * Menggunakan snapshot order_details.menu_name.
     *
     * @return array<int, array{menu_name: string, total_qty: int, total_revenue: int}>
     */
    public static function bestSellers(string $from, string $to, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT 
                od.menu_name,
                COALESCE(SUM(od.qty), 0) AS total_qty,
                COALESCE(SUM(od.subtotal), 0) AS total_revenue
             FROM payments p
             INNER JOIN order_details od ON od.order_id = p.order_id
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             GROUP BY od.menu_name
             ORDER BY total_qty DESC, total_revenue DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':from_date', $from . ' 00:00:00');
        $stmt->bindValue(':to_date',   $to . ' 23:59:59');
        $stmt->bindValue(':limit',     $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $r): array => [
            'menu_name'     => (string) $r['menu_name'],
            'total_qty'     => (int) $r['total_qty'],
            'total_revenue' => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * Pendapatan dan transaksi per tanggal (FR-RPT-05).
     *
     * @return array<int, array{date: string, transaction_count: int, total_revenue: int}>
     */
    public static function daily(string $from, string $to): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT 
                DATE(p.paid_at) AS date,
                COUNT(p.id) AS transaction_count,
                COALESCE(SUM(p.total), 0) AS total_revenue
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             GROUP BY DATE(p.paid_at)
             ORDER BY DATE(p.paid_at) ASC'
        );

        $stmt->execute([
            ':from_date' => $from . ' 00:00:00',
            ':to_date'   => $to . ' 23:59:59',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $r): array => [
            'date'              => (string) $r['date'],
            'transaction_count' => (int) $r['transaction_count'],
            'total_revenue'     => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * Pendapatan per metode pembayaran (cash / qris) (FR-RPT-04).
     *
     * @return array<int, array{method: string, transaction_count: int, total_revenue: int}>
     */
    public static function byMethod(string $from, string $to): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT 
                p.method,
                COUNT(p.id) AS transaction_count,
                COALESCE(SUM(p.total), 0) AS total_revenue
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             GROUP BY p.method
             ORDER BY total_revenue DESC'
        );

        $stmt->execute([
            ':from_date' => $from . ' 00:00:00',
            ':to_date'   => $to . ' 23:59:59',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $r): array => [
            'method'            => (string) $r['method'],
            'transaction_count' => (int) $r['transaction_count'],
            'total_revenue'     => (int) $r['total_revenue'],
        ], $rows);
    }

    /**
     * Jumlah pesanan belum dibayar dan pesanan sedang diproses (untuk kartu ringkasan dashboard).
     *
     * @return array{unpaid_orders: int, processing_orders: int}
     */
    public static function orderCounts(string $from, string $to): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT 
                COUNT(CASE WHEN o.payment_status = \'unpaid\' AND o.status != \'cancelled\' THEN 1 END) AS unpaid_orders,
                COUNT(CASE WHEN o.status = \'processing\' THEN 1 END) AS processing_orders
             FROM orders o
             WHERE o.created_at >= :from_date
               AND o.created_at <= :to_date'
        );

        $stmt->execute([
            ':from_date' => $from . ' 00:00:00',
            ':to_date'   => $to . ' 23:59:59',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'unpaid_orders'     => (int) ($row['unpaid_orders'] ?? 0),
            'processing_orders' => (int) ($row['processing_orders'] ?? 0),
        ];
    }

    /**
     * Menghasilkan file CSV ringkasan transaksi lunas (FR-RPT-07).
     */
    public static function exportCsv(string $from, string $to): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT 
                p.payment_code,
                o.order_code,
                p.paid_at,
                o.customer_name,
                o.order_type,
                u.name AS cashier_name,
                p.method,
                p.total
             FROM payments p
             INNER JOIN orders o ON o.id = p.order_id
             LEFT JOIN users u ON u.id = p.cashier_id
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             ORDER BY p.paid_at ASC'
        );

        $stmt->execute([
            ':from_date' => $from . ' 00:00:00',
            ':to_date'   => $to . ' 23:59:59',
        ]);

        $filename = sprintf('laporan-penjualan-%s-sd-%s.csv', $from, $to);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            return;
        }

        // BOM UTF-8 agar Excel membuka teks dengan benar
        fwrite($output, "\xEF\xBB\xBF");

        // Header CSV
        fputcsv($output, [
            'Kode Pembayaran',
            'Kode Pesanan',
            'Waktu Pembayaran',
            'Nama Pelanggan',
            'Tipe Pesanan',
            'Kasir',
            'Metode Pembayaran',
            'Total (Rp)',
        ]);

        $grandTotal = 0;
        $count = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $grandTotal += (int) $row['total'];
            $count++;

            $typeLabel = ($row['order_type'] === 'dine_in') ? 'Dine In' : 'Takeaway';
            $methodLabel = ($row['method'] === 'cash') ? 'Tunai' : 'QRIS';

            fputcsv($output, [
                $row['payment_code'],
                $row['order_code'],
                $row['paid_at'],
                $row['customer_name'],
                $typeLabel,
                $row['cashier_name'] ?? '-',
                $methodLabel,
                $row['total'],
            ]);
        }

        // Baris ringkasan
        fputcsv($output, []);
        fputcsv($output, ['RINGKASAN']);
        fputcsv($output, ['Periode', $from . ' s.d. ' . $to]);
        fputcsv($output, ['Jumlah Transaksi', $count]);
        fputcsv($output, ['Total Pendapatan', $grandTotal]);

        fclose($output);
        exit;
    }
}
