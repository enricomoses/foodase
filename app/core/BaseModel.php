<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * BaseModel — abstraksi PDO native yang reusable untuk seluruh Model.
 * Menghilangkan duplikasi koneksi, query helper, paginasi, dan generator kode harian.
 */
abstract class BaseModel
{
    /**
     * Ambil koneksi PDO (menggunakan koneksi transaksi aktif jika disediakan).
     */
    protected static function db(?PDO $pdo = null): PDO
    {
        return $pdo ?? Database::connection();
    }

    /**
     * Ambil satu baris hasil query, atau null jika tidak ditemukan.
     *
     * @param array<string|int, mixed> $params
     * @return array<string, mixed>|null
     */
    protected static function fetchOne(string $sql, array $params = [], ?PDO $pdo = null): ?array
    {
        $stmt = self::db($pdo)->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * Ambil seluruh baris hasil query.
     *
     * @param array<string|int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected static function fetchAll(string $sql, array $params = [], ?PDO $pdo = null): array
    {
        $stmt = self::db($pdo)->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Ambil nilai tunggal dari kolom pertama baris pertama.
     *
     * @param array<string|int, mixed> $params
     */
    protected static function fetchValue(string $sql, array $params = [], ?PDO $pdo = null): mixed
    {
        $stmt = self::db($pdo)->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * Jalankan query paginasi standar dengan binding LIMIT/OFFSET bertipe integer.
     *
     * @param array<string, mixed> $params
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    protected static function paginate(
        string $selectSql,
        string $countSql,
        array $params,
        int $page = 1,
        int $perPage = 20,
        ?PDO $pdo = null
    ): array {
        $conn    = self::db($pdo);
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;

        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $dataStmt = $conn->prepare($selectSql . ' LIMIT :_limit OFFSET :_offset');
        foreach ($params as $key => $val) {
            $dataStmt->bindValue($key, $val);
        }
        $dataStmt->bindValue(':_limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':_offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();

        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    /**
     * Hasilkan kode harian berurutan (PREFIX-YYMMDD-NNN) di dalam transaksi dengan FOR UPDATE.
     * Menggunakan ORDER BY id DESC agar urutan tetap akurat meski melampaui 999 per hari.
     */
    protected static function generateDailyCode(
        PDO $pdo,
        string $table,
        string $column,
        string $prefix
    ): string {
        $safeTable  = preg_replace('/[^a-zA-Z0-9_]/', '', $table) ?? '';
        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column) ?? '';
        $dateStr    = date('ymd');
        $pattern    = $prefix . '-' . $dateStr . '-%';

        $stmt = $pdo->prepare(
            "SELECT {$safeColumn}
             FROM {$safeTable}
             WHERE {$safeColumn} LIKE :pattern
             ORDER BY id DESC
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([':pattern' => $pattern]);
        $last = $stmt->fetchColumn();

        $seq = 1;
        if (is_string($last) && $last !== '') {
            $parts = explode('-', $last);
            $seq   = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $dateStr, $seq);
    }

    /**
     * Hitung rentang timestamp sargable [start, end] dari tanggal YYYY-MM-DD
     * agar MySQL dapat memanfaatkan index pada kolom DATETIME.
     *
     * @return array{0: string, 1: string}
     */
    protected static function dayRange(string $date): array
    {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }
}
