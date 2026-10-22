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

    /**
     * Perbarui status alur pesanan (FR-STS-01, BR-06).
     *
     * State machine ketat:
     * - processing -> ready
     * - ready -> completed
     * Selain transisi di atas akan memicu 409 Conflict.
     * Mengunci baris pesanan dalam transaksi dengan FOR UPDATE.
     *
     * @param int    $orderId
     * @param string $next
     *
     * @return array Data pesanan setelah update
     *
     * @throws \RuntimeException
     */
    public static function updateStatus(int $orderId, string $next): array
    {
        return Database::transaction(function (\PDO $pdo) use ($orderId, $next): array {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan tidak ditemukan.',
                    'status'  => 404,
                ]));
            }

            $current = (string) $order['status'];

            $allowed = ($current === 'processing' && $next === 'ready')
                    || ($current === 'ready' && $next === 'completed');

            if (!$allowed) {
                throw new \RuntimeException(json_encode([
                    'message' => "Transisi status dari '{$current}' ke '{$next}' tidak diizinkan (BR-06).",
                    'status'  => 409,
                ]));
            }

            Order::updateStatus($orderId, $next, $pdo);

            return [
                'id'              => $orderId,
                'order_code'      => (string) $order['order_code'],
                'previous_status' => $current,
                'status'          => $next,
            ];
        });
    }

    /**
     * Batalkan pesanan (FR-STS-02, FR-STS-03, BR-10).
     *
     * Aturan BR-10:
     * - pending + unpaid: boleh dibatalkan oleh kasir atau admin.
     * - sudah dibayar (paid) dan belum completed: HANYA boleh oleh admin, alasan wajib minimal 5 karakter,
     *   memanggil PaymentService::void() untuk void pembayaran lalu pesanan menjadi cancelled.
     * - completed: TIDAK BISA dibatalkan (409 Conflict).
     * - Menyimpan cancel_reason dan cancelled_by.
     *
     * @param int         $orderId
     * @param int         $userId
     * @param string      $role     'cashier' | 'admin'
     * @param string|null $reason
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function cancel(
        int $orderId,
        int $userId,
        string $role,
        ?string $reason = null
    ): array {
        $cleanReason = trim((string) $reason);

        return Database::transaction(function (\PDO $pdo) use (
            $orderId,
            $userId,
            $role,
            $cleanReason
        ): array {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan tidak ditemukan.',
                    'status'  => 404,
                ]));
            }

            $status    = (string) $order['status'];
            $payStatus = (string) $order['payment_status'];

            // 1. Cek jika sudah pernah dibatalkan
            if ($status === 'cancelled') {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan ini sudah dibatalkan sebelumnya.',
                    'status'  => 409,
                ]));
            }

            // 2. Completed tidak bisa dibatalkan
            if ($status === 'completed') {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan yang sudah selesai tidak dapat dibatalkan (BR-10).',
                    'status'  => 409,
                ]));
            }

            // 3. Cek apakah pesanan belum dibayar (pending + unpaid)
            $isUnpaidPending = ($status === 'pending' && $payStatus === 'unpaid');

            if ($isUnpaidPending) {
                // Kasir atau admin boleh membatalkan
                if (!in_array($role, ['cashier', 'admin'], true)) {
                    throw new \RuntimeException(json_encode([
                        'message' => 'Akses ditolak. Anda tidak berhak membatalkan pesanan ini.',
                        'status'  => 403,
                    ]));
                }

                $saveReason = $cleanReason !== '' ? $cleanReason : 'Dibatalkan oleh ' . $role;
                Order::cancel($orderId, $saveReason, $userId, $pdo);

                return [
                    'id'            => $orderId,
                    'order_code'    => (string) $order['order_code'],
                    'status'        => 'cancelled',
                    'cancel_reason' => $saveReason,
                    'cancelled_by'  => $userId,
                ];
            }

            // 4. Pesanan sudah dibayar (paid) dan belum completed
            // HANYA admin yang berhak
            if ($role !== 'admin') {
                throw new \RuntimeException(json_encode([
                    'message' => 'Hanya admin yang memiliki wewenang untuk membatalkan pesanan yang sudah dibayar (BR-10).',
                    'status'  => 403,
                ]));
            }

            // Alasan wajib minimal 5 karakter
            if (mb_strlen($cleanReason) < 5) {
                throw new \InvalidArgumentException(json_encode([
                    'message' => 'Alasan pembatalan pesanan yang sudah dibayar wajib diisi minimal 5 karakter.',
                    'errors'  => ['reason' => 'Alasan pembatalan minimal 5 karakter.'],
                ]));
            }

            // Memanggil PaymentService::void()
            PaymentService::void($orderId, $userId, $cleanReason, $pdo);

            // Tandai pesanan sebagai cancelled
            Order::cancel($orderId, $cleanReason, $userId, $pdo);

            return [
                'id'            => $orderId,
                'order_code'    => (string) $order['order_code'],
                'status'        => 'cancelled',
                'cancel_reason' => $cleanReason,
                'cancelled_by'  => $userId,
                'voided'        => true,
            ];
        });
    }
}
