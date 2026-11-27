<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * Model untuk tabel payments.
 */
final class Payment extends BaseModel
{
    public static function create(array $data, ?PDO $pdo = null): int
    {
        $conn = self::db($pdo);
        $stmt = $conn->prepare(
            'INSERT INTO payments
               (payment_code, order_id, method, total, amount_paid, change_amount, status, cashier_id, paid_at)
             VALUES
               (:payment_code, :order_id, :method, :total, :amount_paid, :change_amount, \'paid\', :cashier_id, NOW())'
        );

        $stmt->execute([
            ':payment_code'  => (string) $data['payment_code'],
            ':order_id'      => (int) $data['order_id'],
            ':method'        => (string) $data['method'],
            ':total'         => (int) $data['total'],
            ':amount_paid'   => (int) $data['amount_paid'],
            ':change_amount' => (int) $data['change_amount'],
            ':cashier_id'    => (int) $data['cashier_id'],
        ]);

        return (int) $conn->lastInsertId();
    }

    public static function findByOrderId(int $orderId): ?array
    {
        return self::fetchOne(
            'SELECT p.*, u.name AS cashier_name
             FROM payments p
             LEFT JOIN users u ON u.id = p.cashier_id
             WHERE p.order_id = :order_id
             ORDER BY p.id DESC
             LIMIT 1',
            [':order_id' => $orderId]
        );
    }

    /**
     * Kunci baris pembayaran berdasarkan order_id di dalam transaksi (FOR UPDATE).
     */
    public static function findByOrderIdForUpdate(int $orderId, PDO $pdo): ?array
    {
        return self::fetchOne(
            'SELECT * FROM payments WHERE order_id = :order_id FOR UPDATE',
            [':order_id' => $orderId],
            $pdo
        );
    }

    public static function void(int $orderId, int $userId, string $reason, ?PDO $pdo = null): bool
    {
        $stmt = self::db($pdo)->prepare(
            'UPDATE payments
             SET status = \'void\',
                 voided_at = NOW(),
                 voided_by = :voided_by,
                 void_reason = :void_reason
             WHERE order_id = :order_id'
        );

        return $stmt->execute([
            ':voided_by'   => $userId,
            ':void_reason' => $reason,
            ':order_id'    => $orderId,
        ]);
    }

    public static function findByCode(string $code): ?array
    {
        return self::fetchOne(
            'SELECT p.*, o.order_code, o.customer_name, o.order_type, o.table_number, o.notes, o.source,
                    u.name AS cashier_name
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             LEFT JOIN users u ON u.id = p.cashier_id
             WHERE p.payment_code = :code LIMIT 1',
            [':code' => $code]
        );
    }

    public static function generateCode(PDO $pdo): string
    {
        return self::generateDailyCode($pdo, 'payments', 'payment_code', 'PAY');
    }

    /**
     * Daftar pembayaran dengan filter, paginasi, dan total transaksi lunas (BR-12).
     *
     * @return array{data: array<int, array<string, mixed>>, total: int, paid_total: int, page: int, per_page: int, pages: int}
     */
    public static function list(array $filters = []): array
    {
        $cashierId  = isset($filters['cashier_id']) ? (int) $filters['cashier_id'] : null;
        $method     = $filters['method'] ?? null;
        $statusFilt = (string) ($filters['status'] ?? '');
        $q          = trim((string) ($filters['q'] ?? ''));
        $dateFrom   = $filters['date_from'] ?? null;
        $dateTo     = $filters['date_to'] ?? null;
        $page       = (int) ($filters['page'] ?? 1);
        $perPage    = (int) ($filters['per_page'] ?? 20);

        $where  = [];
        $params = [];

        if ($statusFilt !== '' && in_array($statusFilt, ['paid', 'void'], true)) {
            $where[]           = 'p.status = :status';
            $params[':status'] = $statusFilt;
        }

        if ($cashierId !== null) {
            $where[]               = 'p.cashier_id = :cashier_id';
            $params[':cashier_id'] = $cashierId;
        }

        if (is_string($method) && $method !== '') {
            $where[]           = 'p.method = :method';
            $params[':method'] = $method;
        }

        if ($q !== '') {
            $where[]       = '(p.payment_code LIKE :q OR o.order_code LIKE :q2 OR o.customer_name LIKE :q3)';
            $params[':q']  = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }

        if (is_string($dateFrom) && $dateFrom !== '') {
            $where[]              = 'p.paid_at >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        if (is_string($dateTo) && $dateTo !== '') {
            $where[]            = 'p.paid_at <= :date_to';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Hitung total Rupiah khusus baris 'paid' (tanpa query error jika filter status = 'void')
        if ($statusFilt === 'void') {
            $paidTotal = 0;
        } else {
            $paidWhere  = $where;
            $paidParams = $params;
            if (!isset($paidParams[':status'])) {
                $paidWhere[]               = 'p.status = :paid_status';
                $paidParams[':paid_status'] = 'paid';
            }
            $paidWhereClause = 'WHERE ' . implode(' AND ', $paidWhere);
            $paidTotal = (int) self::fetchValue(
                "SELECT COALESCE(SUM(p.total), 0)
                 FROM payments p
                 JOIN orders o ON o.id = p.order_id
                 {$paidWhereClause}",
                $paidParams
            );
        }

        $paged = self::paginate(
            "SELECT p.id, p.payment_code, p.order_id, p.method, p.total,
                    p.amount_paid, p.change_amount, p.status,
                    p.cashier_id, p.paid_at, p.voided_at, p.void_reason,
                    o.order_code, o.customer_name, o.order_type, o.table_number,
                    u.name AS cashier_name,
                    vu.name AS voided_by_name
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             LEFT JOIN users u  ON u.id  = p.cashier_id
             LEFT JOIN users vu ON vu.id = p.voided_by
             {$whereClause}
             ORDER BY p.paid_at DESC",
            "SELECT COUNT(*) FROM payments p JOIN orders o ON o.id = p.order_id {$whereClause}",
            $params,
            $page,
            $perPage
        );

        $paged['paid_total'] = $paidTotal;
        return $paged;
    }
}
