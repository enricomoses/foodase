<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel payments.
 */
final class Payment
{
    /**
     * Buat record pembayaran baru; kembalikan ID.
     */
    public static function create(array $data, ?\PDO $pdo = null): int
    {
        $pdo  = $pdo ?? Database::connection();
        $stmt = $pdo->prepare(
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

        return (int) $pdo->lastInsertId();
    }

    /**
     * Cari pembayaran berdasarkan order_id.
     */
    public static function findByOrderId(int $orderId): ?array
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT p.*, u.name AS cashier_name
             FROM payments p
             LEFT JOIN users u ON u.id = p.cashier_id
             WHERE p.order_id = :order_id
             ORDER BY p.id DESC
             LIMIT 1'
        );
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Tandai pembayaran sebagai void.
     */
    public static function void(int $orderId, int $userId, string $reason, ?\PDO $pdo = null): bool
    {
        $pdo = $pdo ?? Database::connection();
        $stmt = $pdo->prepare(
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

    /**
     * Cari pembayaran berdasarkan payment_code (PAY-YYMMDD-NNN).
     */
    public static function findByCode(string $code): ?array
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT p.*, o.order_code, o.customer_name, o.order_type, o.table_number, o.notes, o.source,
                    u.name AS cashier_name
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             LEFT JOIN users u ON u.id = p.cashier_id
             WHERE p.payment_code = :code LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Hasilkan kode pembayaran PAY-YYMMDD-NNN yang unik.
     * Harus dipanggil di dalam transaksi dengan SELECT ... FOR UPDATE.
     *
     * @param \PDO $pdo Koneksi PDO yang sedang aktif dalam transaksi.
     * @return string   Kode pembayaran, mis. "PAY-261022-007"
     */
    public static function generateCode(\PDO $pdo): string
    {
        $prefix  = 'PAY';
        $dateStr = date('ymd'); // YYMMDD

        $stmt = $pdo->prepare(
            "SELECT payment_code FROM payments
             WHERE payment_code LIKE :pattern
             ORDER BY payment_code DESC
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([':pattern' => $prefix . '-' . $dateStr . '-%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $parts = explode('-', (string) $last);
            $seq   = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $dateStr, $seq);
    }

    /**
     * Daftar pembayaran dengan filter dan paginasi.
     * Kasir hanya dapat melihat pembayaran miliknya (cashier_id) — dipaksa dari luar.
     *
     * Filter tambahan:
     *   status        string  'paid' | 'void' | '' (semua, default)
     *   cashier_id    int     Batasi ke kasir tertentu
     *   method        string  'cash' | 'qris'
     *   q             string  Cari kode bayar, kode pesanan, nama pelanggan
     *   date_from     string  YYYY-MM-DD
     *   date_to       string  YYYY-MM-DD
     *   page          int     Halaman (default 1)
     *   per_page      int     Baris per halaman (default 20, maks 100)
     *
     * Return:
     *   data, total, page, per_page, pages, paid_total (sum total baris paid dalam filter)
     */
    public static function search(array $filters = []): array
    {
        $pdo = Database::connection();

        $cashierId  = isset($filters['cashier_id']) ? (int) $filters['cashier_id'] : null;
        $method     = $filters['method'] ?? null;
        $statusFilt = $filters['status'] ?? '';   // '' = semua status
        $q          = trim((string) ($filters['q'] ?? ''));
        $dateFrom   = $filters['date_from'] ?? null;
        $dateTo     = $filters['date_to'] ?? null;
        $page       = max(1, (int) ($filters['page'] ?? 1));
        $perPage    = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        $where  = [];
        $params = [];

        // Filter status — jika tidak diisi, ambil semua status
        if ($statusFilt !== '' && in_array($statusFilt, ['paid', 'void'], true)) {
            $where[]            = 'p.status = :status';
            $params[':status']  = $statusFilt;
        }

        // Scope kasir
        if ($cashierId !== null) {
            $where[]              = 'p.cashier_id = :cashier_id';
            $params[':cashier_id'] = $cashierId;
        }

        if ($method !== null && $method !== '') {
            $where[]           = 'p.method = :method';
            $params[':method'] = $method;
        }

        if ($q !== '') {
            $where[]       = '(p.payment_code LIKE :q OR o.order_code LIKE :q2 OR o.customer_name LIKE :q3)';
            $params[':q']  = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $where[]              = 'DATE(p.paid_at) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }

        if ($dateTo !== null && $dateTo !== '') {
            $where[]            = 'DATE(p.paid_at) <= :date_to';
            $params[':date_to'] = $dateTo;
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total baris (untuk pagination)
        $countStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM payments p
             JOIN orders o ON o.id = p.order_id
             {$whereClause}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Jumlah total Rupiah yang sudah paid pada filter yang sama
        // Selalu gunakan kondisi paid (tanpa menimpa filter status di atas)
        $paidWhere = $where;
        $paidParams = $params;
        // Pastikan kita menghitung SUM hanya untuk baris paid
        if (!isset($paidParams[':status'])) {
            $paidWhere[]            = 'p.status = :paid_status';
            $paidParams[':paid_status'] = 'paid';
        } elseif ($paidParams[':status'] !== 'paid') {
            // Filter status = void → paid_total pasti 0
            $paidParams = [':zero' => 1]; // Tidak ada baris paid
            $paidWhere  = ['1 = 0'];
        }
        $paidWhereClause = $paidWhere ? ('WHERE ' . implode(' AND ', $paidWhere)) : '';

        $sumStmt = $pdo->prepare(
            "SELECT COALESCE(SUM(p.total), 0) FROM payments p
             JOIN orders o ON o.id = p.order_id
             {$paidWhereClause}"
        );
        $sumStmt->execute($paidParams);
        $paidTotal = (int) $sumStmt->fetchColumn();

        // Ambil baris dengan paginasi
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare(
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
             ORDER BY p.paid_at DESC
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
            'data'       => $rows,
            'total'      => $total,
            'paid_total' => $paidTotal,
            'page'       => $page,
            'per_page'   => $perPage,
            'pages'      => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    /**
     * Alias backward-compatible untuk Payment::search() dengan status default 'paid'.
     * Dipanggil oleh PaymentController::list() lama (report, dll).
     */
    public static function list(array $filters = []): array
    {
        // Default ke paid saja agar tidak memutus caller yang sudah ada
        if (!isset($filters['status'])) {
            $filters['status'] = 'paid';
        }
        return self::search($filters);
    }
}
