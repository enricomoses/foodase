<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use PDOException;

/**
 * OrderService — logika bisnis untuk pembuatan dan pengelolaan pesanan.
 *
 * Dapat dipakai ulang oleh:
 * - api/orders/create.php      (pelanggan online)
 * - api/orders/create-pos.php  (kasir POS)
 */
final class OrderService
{
    /**
     * Buat pesanan baru secara atomik.
     *
     * Validasi (BR-01..05, BR-16):
     * - customer_name: wajib, 2–50 karakter (BR-05)
     * - order_type: dine_in | takeaway (BR-04)
     * - table_number: wajib jika dine_in, rentang 1..max_table (BR-04)
     * - notes: opsional, maks 255 karakter (BR-05)
     * - items: array [{menu_id, qty}], maks 30 jenis, qty 1–99 (BR-03)
     * - Setiap menu_id harus ada, tidak dihapus, dan tersedia (BR-02)
     * - Total dihitung dari DB, bukan dari client (BR-01)
     * - order_code FE-YYMMDD-NNN dibuat dengan FOR UPDATE (BR-16)
     *
     * @param array    $input       Data dari client (customer_name, order_type, …, items).
     * @param int|null $createdBy   User ID kasir jika dari POS, null untuk online.
     * @param string   $source      'online' | 'cashier'.
     *
     * @return array   ['order_code' => string, 'total' => int, 'order_id' => int]
     *
     * @throws \InvalidArgumentException  Validasi gagal (pesan + field errors).
     * @throws \RuntimeException          Kesalahan DB atau konflik kode.
     */
    public static function create(
        array $input,
        ?int $createdBy = null,
        string $source = 'online'
    ): array {
        /* ── Validasi Awal ─────────────────────────────────────────── */
        $errors = [];

        $customerName = trim((string) ($input['customer_name'] ?? ''));
        if ($customerName === '') {
            $errors['customer_name'] = 'Nama pelanggan wajib diisi.';
        } elseif (mb_strlen($customerName) < 2) {
            $errors['customer_name'] = 'Nama minimal 2 karakter.';
        } elseif (mb_strlen($customerName) > 50) {
            $errors['customer_name'] = 'Nama maksimal 50 karakter.';
        }

        $orderType = trim((string) ($input['order_type'] ?? ''));
        if (!in_array($orderType, ['dine_in', 'takeaway'], true)) {
            $errors['order_type'] = 'Tipe pesanan tidak valid. Pilih dine_in atau takeaway.';
        }

        $maxTable   = (int) Config::get('order.max_table', 30);
        $tableNumber = null;
        if ($orderType === 'dine_in') {
            $tn = $input['table_number'] ?? null;
            if ($tn === null || $tn === '') {
                $errors['table_number'] = 'Nomor meja wajib diisi untuk makan di tempat.';
            } else {
                $tableNumber = (int) $tn;
                if ($tableNumber < 1 || $tableNumber > $maxTable) {
                    $errors['table_number'] = "Nomor meja harus antara 1 dan {$maxTable}.";
                }
            }
        }

        $notes = trim((string) ($input['notes'] ?? ''));
        if ($notes !== '' && mb_strlen($notes) > 255) {
            $errors['notes'] = 'Catatan maksimal 255 karakter.';
        }

        $rawItems = $input['items'] ?? [];
        if (!is_array($rawItems) || count($rawItems) === 0) {
            $errors['items'] = 'Keranjang kosong. Tambahkan menu terlebih dahulu.';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                json_encode(['message' => 'Data tidak valid.', 'errors' => $errors])
            );
        }

        /* ── Validasi Items ────────────────────────────────────────── */
        $maxItemKind = (int) Config::get('order.max_item_kind', 30);
        $maxQty      = (int) Config::get('order.max_qty', 99);

        if (count($rawItems) > $maxItemKind) {
            throw new \InvalidArgumentException(json_encode([
                'message' => "Maksimal {$maxItemKind} jenis menu per pesanan.",
                'errors'  => ['items' => "Maksimal {$maxItemKind} jenis menu per pesanan."],
            ]));
        }

        // Normalisasi & deduplikasi item berdasarkan menu_id
        $itemMap = [];
        foreach ($rawItems as $it) {
            $menuId = (int) ($it['menu_id'] ?? 0);
            $qty    = (int) ($it['qty'] ?? 0);

            if ($menuId <= 0 || $qty < 1 || $qty > $maxQty) {
                throw new \InvalidArgumentException(json_encode([
                    'message' => 'Data item tidak valid.',
                    'errors'  => ['items' => 'Setiap item harus memiliki menu_id valid dan jumlah 1–' . $maxQty . '.'],
                ]));
            }

            if (isset($itemMap[$menuId])) {
                $itemMap[$menuId] += $qty;
            } else {
                $itemMap[$menuId] = $qty;
            }
        }

        /* ── Ambil Menu dari DB & Hitung Total (BR-01, BR-02) ──────── */
        $menuIds   = array_keys($itemMap);
        $validated = [];
        $total     = 0;

        foreach ($menuIds as $menuId) {
            $menu = Menu::find($menuId); // tidak termasuk soft-deleted

            if (!$menu) {
                throw new \InvalidArgumentException(json_encode([
                    'message' => "Menu dengan ID {$menuId} tidak ditemukan atau sudah dihapus.",
                    'errors'  => ['items' => "Menu dengan ID {$menuId} tidak ditemukan."],
                ]));
            }

            if (!(bool) $menu['is_available']) {
                throw new \InvalidArgumentException(json_encode([
                    'message' => "Menu \"{$menu['name']}\" sedang tidak tersedia (Habis).",
                    'errors'  => ['items' => "Menu \"{$menu['name']}\" tidak tersedia."],
                ]));
            }

            $qty      = $itemMap[$menuId];
            $price    = (int) $menu['price'];
            $subtotal = $price * $qty;
            $total   += $subtotal;

            $validated[] = [
                'menu_id'   => $menuId,
                'menu_name' => $menu['name'],
                'price'     => $price,
                'qty'       => $qty,
                'subtotal'  => $subtotal,
            ];
        }

        /* ── Simpan di Transaksi Atomik (BR-16, FR-CHK-03) ─────────── */
        return Database::transaction(function (\PDO $pdo) use (
            $customerName,
            $orderType,
            $tableNumber,
            $notes,
            $source,
            $createdBy,
            $total,
            $validated
        ): array {
            // Generate kode dengan FOR UPDATE untuk mencegah duplicate (BR-16)
            $maxRetries = 3;
            $orderCode  = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    $orderCode = Order::generateCode($pdo);

                    $orderId = Order::create([
                        'order_code'    => $orderCode,
                        'customer_name' => $customerName,
                        'order_type'    => $orderType,
                        'table_number'  => $tableNumber,
                        'notes'         => $notes,
                        'source'        => $source,
                        'total'         => $total,
                        'created_by'    => $createdBy,
                    ]);

                    break;
                } catch (PDOException $e) {
                    // Duplicate key pada order_code — coba lagi
                    if ($e->getCode() === '23000' && $attempt < $maxRetries - 1) {
                        continue;
                    }
                    throw $e;
                }
            }

            if (!isset($orderId) || !isset($orderCode)) {
                throw new \RuntimeException('Gagal membuat kode pesanan unik setelah beberapa percobaan.');
            }

            // Simpan detail (snapshot nama dan harga — BR-11)
            foreach ($validated as $item) {
                OrderDetail::create([
                    'order_id'  => $orderId,
                    'menu_id'   => $item['menu_id'],
                    'menu_name' => $item['menu_name'],
                    'price'     => $item['price'],
                    'qty'       => $item['qty'],
                    'subtotal'  => $item['subtotal'],
                ]);
            }

            return [
                'order_id'   => $orderId,
                'order_code' => $orderCode,
                'total'      => $total,
            ];
        });
    }
}
