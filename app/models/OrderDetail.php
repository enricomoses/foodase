<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * Model untuk tabel order_details.
 */
final class OrderDetail extends BaseModel
{
    public static function create(array $data, ?PDO $pdo = null): int
    {
        $conn     = self::db($pdo);
        $qty      = (int) $data['qty'];
        $price    = (int) $data['price'];
        $subtotal = $price * $qty;

        $stmt = $conn->prepare(
            'INSERT INTO order_details (order_id, menu_id, menu_name, price, qty, subtotal)
             VALUES (:order_id, :menu_id, :menu_name, :price, :qty, :subtotal)'
        );
        $stmt->execute([
            ':order_id'  => (int) $data['order_id'],
            ':menu_id'   => (int) $data['menu_id'],
            ':menu_name' => (string) $data['menu_name'],
            ':price'     => $price,
            ':qty'       => $qty,
            ':subtotal'  => $subtotal,
        ]);

        return (int) $conn->lastInsertId();
    }

    /**
     * Ambil semua detail untuk satu pesanan.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function byOrder(int $orderId): array
    {
        return self::fetchAll(
            'SELECT od.*, m.image, m.is_available
             FROM order_details od
             LEFT JOIN menus m ON m.id = od.menu_id
             WHERE od.order_id = :order_id
             ORDER BY od.id ASC',
            [':order_id' => $orderId]
        );
    }

    /**
     * Ambil detail untuk banyak pesanan sekaligus dalam 1 query (menghilangkan N+1).
     *
     * @param int[] $orderIds
     * @return array<int, array<int, array<string, mixed>>> Dikelompokkan per order_id
     */
    public static function byOrderIds(array $orderIds): array
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $orderIds), static fn(int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $rows = self::fetchAll(
            "SELECT od.*, m.image, m.is_available
             FROM order_details od
             LEFT JOIN menus m ON m.id = od.menu_id
             WHERE od.order_id IN ({$placeholders})
             ORDER BY od.order_id ASC, od.id ASC",
            $cleanIds
        );

        $grouped = [];
        foreach ($rows as $row) {
            $oid = (int) $row['order_id'];
            $grouped[$oid][] = $row;
        }
        return $grouped;
    }
}
