<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\Config;
use PDO;

/**
 * Model untuk tabel orders.
 */
final class Order extends BaseModel
{
    public static function create(array $data, ?PDO $pdo = null): int
    {
        $conn = self::db($pdo);
        $stmt = $conn->prepare(
            'INSERT INTO orders
               (order_code, customer_name, order_type, table_number, notes, source,
                status, payment_status, total, created_by)
             VALUES
               (:order_code, :customer_name, :order_type, :table_number, :notes, :source,
                \'pending\', \'unpaid\', :total, :created_by)'
        );

        $stmt->execute([
            ':order_code'    => (string) $data['order_code'],
            ':customer_name' => (string) $data['customer_name'],
            ':order_type'    => (string) $data['order_type'],
            ':table_number'  => isset($data['table_number']) && $data['table_number'] !== null
                ? (int) $data['table_number'] : null,
            ':notes'         => isset($data['notes']) && $data['notes'] !== ''
                ? (string) $data['notes'] : null,
            ':source'        => (string) ($data['source'] ?? 'online'),
            ':total'         => (int) $data['total'],
            ':created_by'    => isset($data['created_by']) ? (int) $data['created_by'] : null,
        ]);

        return (int) $conn->lastInsertId();
    }

    public static function findByCode(string $code): ?array
    {
        return self::fetchOne(
            'SELECT o.*, u_c.name AS cancelled_by_name
             FROM orders o
             LEFT JOIN users u_c ON u_c.id = o.cancelled_by
             WHERE o.order_code = :code LIMIT 1',
            [':code' => $code]
        );
    }

    public static function find(int $id): ?array
    {
        return self::fetchOne(
            'SELECT o.*, u_c.name AS cancelled_by_name
             FROM orders o
             LEFT JOIN users u_c ON u_c.id = o.cancelled_by
             WHERE o.id = :id LIMIT 1',
            [':id' => $id]
        );
    }

    /**
     * Kunci baris pesanan di dalam transaksi DB (SELECT ... FOR UPDATE).
     */
    public static function findForUpdate(int $id, PDO $pdo): ?array
    {
        return self::fetchOne(
            'SELECT * FROM orders WHERE id = :id FOR UPDATE',
            [':id' => $id],
            $pdo
        );
    }

    /**
     * Ambil banyak pesanan berdasarkan daftar kode pesanan.
     *
     * @param string[] $codes
     * @return array<int, array<string, mixed>>
     */
    public static function findManyByCodes(array $codes): array
    {
        $cleanCodes = array_values(array_filter(array_unique($codes)));
        if (empty($cleanCodes)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanCodes), '?'));
        return self::fetchAll(
            "SELECT * FROM orders WHERE order_code IN ({$placeholders}) ORDER BY created_at DESC",
            $cleanCodes
        );
    }

    /**
     * Hasilkan kode pesanan unik berdasarkan prefix config (default FE-YYMMDD-NNN).
     */
    public static function generateCode(PDO $pdo): string
    {
        $prefix = (string) Config::get('order.code_prefix', 'FE');
        return self::generateDailyCode($pdo, 'orders', 'order_code', $prefix);
    }

    /**
     * Daftar pesanan dengan filter dan paginasi (menggunakan range tanggal sargable).
     *
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function list(array $filters = []): array
    {
        $status    = $filters['status'] ?? null;
        $payStatus = $filters['payment_status'] ?? null;
        $q         = trim((string) ($filters['q'] ?? ''));
        $orderType = $filters['order_type'] ?? ($filters['type'] ?? null);
        $date      = $filters['date'] ?? null;
        $createdBy = isset($filters['created_by']) ? (int) $filters['created_by'] : null;
        $page      = (int) ($filters['page'] ?? 1);
        $perPage   = (int) ($filters['per_page'] ?? 20);

        $where  = [];
        $params = [];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $where[]           = 'o.status = :status';
            $params[':status'] = $status;
        }

        if ($payStatus !== null && $payStatus !== '') {
            $where[]              = 'o.payment_status = :pay_status';
            $params[':pay_status'] = $payStatus;
        }

        if ($q !== '') {
            $where[]       = '(o.order_code LIKE :q OR o.customer_name LIKE :q2)';
            $params[':q']  = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        if ($orderType !== null && $orderType !== '' && $orderType !== 'all') {
            $where[]              = 'o.order_type = :order_type';
            $params[':order_type'] = $orderType;
        }

        if (is_string($date) && $date !== '') {
            [$start, $end]         = self::dayRange($date);
            $where[]               = 'o.created_at >= :date_start AND o.created_at <= :date_end';
            $params[':date_start'] = $start;
            $params[':date_end']   = $end;
        }

        if ($createdBy !== null) {
            $where[]               = 'o.created_by = :created_by';
            $params[':created_by'] = $createdBy;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        return self::paginate(
            "SELECT o.* FROM orders o {$whereClause} ORDER BY o.created_at DESC",
            "SELECT COUNT(*) FROM orders o {$whereClause}",
            $params,
            $page,
            $perPage
        );
    }

    /**
     * Hitung jumlah pesanan per status tab kasir.
     */
    public static function tabCounts(array $filters = []): array
    {
        $date   = $filters['date'] ?? null;
        $where  = [];
        $params = [];

        if (is_string($date) && $date !== '') {
            [$start, $end]         = self::dayRange($date);
            $where[]               = 'created_at >= :date_start AND created_at <= :date_end';
            $params[':date_start'] = $start;
            $params[':date_end']   = $end;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt        = self::db()->prepare("SELECT status, COUNT(*) AS cnt FROM orders {$whereClause} GROUP BY status");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'pending'    => (int) ($rows['pending'] ?? 0),
            'processing' => (int) ($rows['processing'] ?? 0),
            'ready'      => (int) ($rows['ready'] ?? 0),
            'completed'  => (int) ($rows['completed'] ?? 0),
            'cancelled'  => (int) ($rows['cancelled'] ?? 0),
        ];
    }

    /**
     * Tandai pesanan sudah dibayar dan masuk tahap processing dalam transaksi pembayaran (BR-07).
     */
    public static function markPaid(int $id, ?PDO $pdo = null): bool
    {
        $stmt = self::db($pdo)->prepare(
            'UPDATE orders SET payment_status = \'paid\', status = \'processing\', updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    public static function updateStatus(int $id, string $status, ?PDO $pdo = null): bool
    {
        $stmt = self::db($pdo)->prepare(
            'UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);
    }

    public static function cancel(int $id, ?string $reason = null, ?int $cancelledBy = null, ?PDO $pdo = null): bool
    {
        $stmt = self::db($pdo)->prepare(
            'UPDATE orders
             SET status = \'cancelled\',
                 cancel_reason = :reason,
                 cancelled_by = :cancelled_by,
                 updated_at = NOW()
             WHERE id = :id'
        );
        return $stmt->execute([
            ':reason'       => $reason,
            ':cancelled_by' => $cancelledBy,
            ':id'           => $id,
        ]);
    }
}
