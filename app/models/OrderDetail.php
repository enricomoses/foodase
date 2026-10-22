<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model untuk tabel order_details.
 */
final class OrderDetail
{
    /**
     * Sisipkan satu baris detail pesanan.
     */
    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO order_details (order_id, menu_id, menu_name, price, qty, subtotal)
             VALUES (:order_id, :menu_id, :menu_name, :price, :qty, :subtotal)'
        );

        $qty      = (int) $data['qty'];
        $price    = (int) $data['price'];
        $subtotal = $price * $qty;

        $stmt->execute([
            ':order_id'  => (int) $data['order_id'],
            ':menu_id'   => (int) $data['menu_id'],
            ':menu_name' => (string) $data['menu_name'],
            ':price'     => $price,
            ':qty'       => $qty,
            ':subtotal'  => $subtotal,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Ambil semua detail untuk satu pesanan.
     */
    public static function byOrder(int $orderId): array
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT od.*, m.image, m.is_available
             FROM order_details od
             LEFT JOIN menus m ON m.id = od.menu_id
             WHERE od.order_id = :order_id
             ORDER BY od.id ASC'
        );
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
