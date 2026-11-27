<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * Model agregat laporan penjualan (memisahkan seluruh kueri SQL dari ReportService).
 * Aturan BR-12: hanya menghitung pembayaran berstatus 'paid'.
 */
final class Report extends BaseModel
{
    /**
     * @return array<string, mixed>
     */
    public static function summary(string $from, string $to): array
    {
        return self::fetchOne(
            'SELECT
                COALESCE(SUM(p.total), 0) AS total_revenue,
                COUNT(p.id) AS transaction_count,
                COALESCE(ROUND(AVG(p.total)), 0) AS average_per_transaction
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date',
            [
                ':from_date' => $from . ' 00:00:00',
                ':to_date'   => $to . ' 23:59:59',
            ]
        ) ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function bestSellers(string $from, string $to, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        $stmt  = self::db()->prepare(
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
        $stmt->bindValue(':to_date', $to . ' 23:59:59');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function daily(string $from, string $to): array
    {
        return self::fetchAll(
            'SELECT
                DATE(p.paid_at) AS date,
                COUNT(p.id) AS transaction_count,
                COALESCE(SUM(p.total), 0) AS total_revenue
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             GROUP BY DATE(p.paid_at)
             ORDER BY DATE(p.paid_at) ASC',
            [
                ':from_date' => $from . ' 00:00:00',
                ':to_date'   => $to . ' 23:59:59',
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function byMethod(string $from, string $to): array
    {
        return self::fetchAll(
            'SELECT
                p.method,
                COUNT(p.id) AS transaction_count,
                COALESCE(SUM(p.total), 0) AS total_revenue
             FROM payments p
             WHERE p.status = \'paid\'
               AND p.paid_at >= :from_date
               AND p.paid_at <= :to_date
             GROUP BY p.method
             ORDER BY total_revenue DESC',
            [
                ':from_date' => $from . ' 00:00:00',
                ':to_date'   => $to . ' 23:59:59',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderCounts(string $from, string $to): array
    {
        return self::fetchOne(
            'SELECT
                COUNT(CASE WHEN o.payment_status = \'unpaid\' AND o.status != \'cancelled\' THEN 1 END) AS unpaid_orders,
                COUNT(CASE WHEN o.status = \'processing\' THEN 1 END) AS processing_orders
             FROM orders o
             WHERE o.created_at >= :from_date
               AND o.created_at <= :to_date',
            [
                ':from_date' => $from . ' 00:00:00',
                ':to_date'   => $to . ' 23:59:59',
            ]
        ) ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function paidTransactions(string $from, string $to): array
    {
        return self::fetchAll(
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
             ORDER BY p.paid_at ASC',
            [
                ':from_date' => $from . ' 00:00:00',
                ':to_date'   => $to . ' 23:59:59',
            ]
        );
    }
}
