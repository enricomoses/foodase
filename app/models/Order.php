<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel orders.
 */
final class Order
{
    /**
     * Buat pesanan baru; kembalikan ID baris yang baru dibuat.
     */
    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
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

        return (int) $pdo->lastInsertId();
    }

    /**
     * Cari pesanan berdasarkan kode (FE-YYMMDD-NNN).
     */
    public static function findByCode(string $code): ?array
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT o.*, u_c.name AS cancelled_by_name
             FROM orders o
             LEFT JOIN users u_c ON u_c.id = o.cancelled_by
             WHERE o.order_code = :code LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Cari pesanan berdasarkan ID.
     */
    public static function find(int $id): ?array
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT o.*, u_c.name AS cancelled_by_name
             FROM orders o
             LEFT JOIN users u_c ON u_c.id = o.cancelled_by
             WHERE o.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ambil banyak pesanan berdasarkan daftar kode pesanan.
     * Diurutkan dari yang paling baru dibuat.
     *
     * @param string[] $codes
     * @return array
     */
    public static function findManyByCodes(array $codes): array
    {
        $cleanCodes = array_values(array_filter(array_unique($codes)));
        if (empty($cleanCodes)) {
            return [];
        }

        $pdo = Database::connection();
        $placeholders = implode(',', array_fill(0, count($cleanCodes), '?'));
        $stmt = $pdo->prepare(
            "SELECT * FROM orders
             WHERE order_code IN ({$placeholders})
             ORDER BY created_at DESC"
        );
        $stmt->execute($cleanCodes);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hasilkan kode pesanan FE-YYMMDD-NNN yang unik.
     * Harus dipanggil di dalam transaksi dengan SELECT ... FOR UPDATE.
     *
     * @param \PDO $pdo   Koneksi PDO yang sedang aktif dalam transaksi.
     * @return string     Kode pesanan, mis. "FE-261022-007"
     */
    public static function generateCode(\PDO $pdo): string
    {
        $prefix  = 'FE';
        $dateStr = date('ymd'); // YYMMDD

        // Kunci baris hari ini untuk mencegah race condition
        $stmt = $pdo->prepare(
            "SELECT order_code FROM orders
             WHERE order_code LIKE :pattern
             ORDER BY order_code DESC
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([':pattern' => $prefix . '-' . $dateStr . '-%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            // Ambil urutan terakhir dan tambah 1
            $parts = explode('-', (string) $last);
            $seq   = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $dateStr, $seq);
    }

    /**
     * Daftar pesanan dengan filter dan paginasi.
     */
    public static function list(array $filters = []): array
    {
        $pdo = Database::connection();

        $status        = $filters['status'] ?? null;
        $payStatus     = $filters['payment_status'] ?? null;
        $q             = trim((string) ($filters['q'] ?? ''));
        $orderType     = $filters['order_type'] ?? null;
        $date          = $filters['date'] ?? null;
        $createdBy     = isset($filters['created_by']) ? (int) $filters['created_by'] : null;
        $page          = max(1, (int) ($filters['page'] ?? 1));
        $perPage       = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        $where  = [];
        $params = [];

        if ($status !== null) {
            $where[]           = 'o.status = :status';
            $params[':status'] = $status;
        }

        if ($payStatus !== null) {
            $where[]              = 'o.payment_status = :pay_status';
            $params[':pay_status'] = $payStatus;
        }

        if ($q !== '') {
            $where[]      = '(o.order_code LIKE :q OR o.customer_name LIKE :q2)';
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        if ($orderType !== null) {
            $where[]              = 'o.order_type = :order_type';
            $params[':order_type'] = $orderType;
        }

        if ($date !== null) {
            $where[]       = 'DATE(o.created_at) = :date';
            $params[':date'] = $date;
        }

        if ($createdBy !== null) {
            $where[]               = 'o.created_by = :created_by';
            $params[':created_by'] = $createdBy;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare(
            "SELECT o.* FROM orders o {$whereClause}
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    /**
     * Cari pesanan dengan filter status, q, type, date, pagination (FR-COR-02).
     */
    public static function search(array $filters = []): array
    {
        if (isset($filters['type']) && !isset($filters['order_type'])) {
            $filters['order_type'] = $filters['type'];
        }
        return self::list($filters);
    }

    /**
     * Hitung jumlah pesanan per status tab kasir.
     */
    public static function tabCounts(array $filters = []): array
    {
        $pdo = Database::connection();
        $date = $filters['date'] ?? null;
        $where = [];
        $params = [];

        if ($date !== null && $date !== '') {
            $where[] = 'DATE(created_at) = :date';
            $params[':date'] = $date;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM orders {$whereClause} GROUP BY status");
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
     * Perbarui status pesanan.
     */
    public static function updateStatus(int $id, string $status, ?\PDO $pdo = null): bool
    {
        $pdo = $pdo ?? Database::connection();
        $stmt = $pdo->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);
    }

    /**
     * Tandai pesanan sebagai dibatalkan (cancelled).
     */
    public static function cancel(int $id, ?string $reason = null, ?int $cancelledBy = null, ?\PDO $pdo = null): bool
    {
        $pdo = $pdo ?? Database::connection();
        $stmt = $pdo->prepare(
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
