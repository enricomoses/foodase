<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\ForbiddenException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use PDO;
use PDOException;

/**
 * OrderService — logika bisnis pembuatan, perubahan status, dan pembatalan pesanan.
 * Memenuhi BR-01..06, BR-10, BR-11, BR-16 tanpa raw SQL di layer service.
 */
final class OrderService
{
    /**
     * Buat pesanan baru secara atomik (dipakai oleh checkout online & POS kasir).
     *
     * @return array{order_id: int, order_code: string, total: int}
     */
    public static function create(
        array $input,
        ?int $createdBy = null,
        string $source = 'online'
    ): array {
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

        $maxTable    = (int) Config::get('order.max_table', 30);
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
            throw new ValidationException('Data tidak valid.', $errors);
        }

        $maxItemKind = (int) Config::get('order.max_item_kind', 30);
        $maxQty      = (int) Config::get('order.max_qty', 99);

        // Normalisasi, deduplikasi, dan validasi ketat BR-03 (termasuk setelah penggabungan duplikat)
        $itemMap = [];
        foreach ($rawItems as $it) {
            $menuId = is_array($it) ? (int) ($it['menu_id'] ?? 0) : 0;
            $qty    = is_array($it) ? (int) ($it['qty'] ?? 0) : 0;

            if ($menuId <= 0 || $qty < 1 || $qty > $maxQty) {
                throw new ValidationException('Data item tidak valid.', [
                    'items' => "Setiap item harus memiliki menu_id valid dan jumlah 1–{$maxQty}.",
                ]);
            }

            $mergedQty = ($itemMap[$menuId] ?? 0) + $qty;
            if ($mergedQty > $maxQty) {
                throw new ValidationException("Jumlah per item tidak boleh melebihi {$maxQty} (BR-03).", [
                    'items' => "Jumlah per item maksimal {$maxQty}.",
                ]);
            }
            $itemMap[$menuId] = $mergedQty;
        }

        if (count($itemMap) > $maxItemKind) {
            throw new ValidationException("Maksimal {$maxItemKind} jenis menu per pesanan.", [
                'items' => "Maksimal {$maxItemKind} jenis menu per pesanan.",
            ]);
        }

        // Batch fetch seluruh menu dalam 1 query (BR-01, BR-02 — nol N+1 query)
        $menuIds   = array_keys($itemMap);
        $menusById = Menu::findByIds($menuIds);
        $validated = [];
        $total     = 0;

        foreach ($menuIds as $menuId) {
            $menu = $menusById[$menuId] ?? null;
            if (!$menu) {
                throw new ValidationException("Menu dengan ID {$menuId} tidak ditemukan atau sudah dihapus.", [
                    'items' => "Menu dengan ID {$menuId} tidak ditemukan.",
                ]);
            }

            if (!(bool) $menu['is_available']) {
                throw new ValidationException("Menu \"{$menu['name']}\" sedang tidak tersedia (Habis).", [
                    'items' => "Menu \"{$menu['name']}\" tidak tersedia.",
                ]);
            }

            $qty      = $itemMap[$menuId];
            $price    = (int) $menu['price'];
            $subtotal = $price * $qty;
            $total   += $subtotal;

            $validated[] = [
                'menu_id'   => $menuId,
                'menu_name' => (string) $menu['name'],
                'price'     => $price,
                'qty'       => $qty,
                'subtotal'  => $subtotal,
            ];
        }

        // Simpan pesanan + detail snapshot secara atomik (BR-11, BR-16)
        return Database::transaction(function (PDO $pdo) use (
            $customerName,
            $orderType,
            $tableNumber,
            $notes,
            $source,
            $createdBy,
            $total,
            $validated
        ): array {
            $maxRetries = 3;
            $orderCode  = null;
            $orderId    = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    $orderCode = Order::generateCode($pdo);
                    $orderId   = Order::create([
                        'order_code'    => $orderCode,
                        'customer_name' => $customerName,
                        'order_type'    => $orderType,
                        'table_number'  => $tableNumber,
                        'notes'         => $notes,
                        'source'        => $source,
                        'total'         => $total,
                        'created_by'    => $createdBy,
                    ], $pdo);
                    break;
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000' && $attempt < $maxRetries - 1) {
                        continue;
                    }
                    throw $e;
                }
            }

            if ($orderId === null || $orderCode === null) {
                throw new AppException('Gagal membuat kode pesanan unik setelah beberapa percobaan.', 500);
            }

            foreach ($validated as $item) {
                OrderDetail::create([
                    'order_id'  => $orderId,
                    'menu_id'   => $item['menu_id'],
                    'menu_name' => $item['menu_name'],
                    'price'     => $item['price'],
                    'qty'       => $item['qty'],
                    'subtotal'  => $item['subtotal'],
                ], $pdo);
            }

            return [
                'order_id'   => $orderId,
                'order_code' => $orderCode,
                'total'      => $total,
            ];
        });
    }

    /**
     * Perbarui status alur pesanan (BR-06: processing -> ready -> completed).
     */
    public static function updateStatus(int $orderId, string $next): array
    {
        return Database::transaction(function (PDO $pdo) use ($orderId, $next): array {
            $order = Order::findForUpdate($orderId, $pdo);
            if (!$order) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            $current = (string) $order['status'];
            $allowed = ($current === 'processing' && $next === 'ready')
                || ($current === 'ready' && $next === 'completed');

            if (!$allowed) {
                throw new ConflictException("Transisi status dari '{$current}' ke '{$next}' tidak diizinkan (BR-06).");
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
     * Batalkan pesanan sesuai aturan BR-10.
     */
    public static function cancel(
        int $orderId,
        int $userId,
        string $role,
        ?string $reason = null
    ): array {
        $cleanReason = trim((string) $reason);

        return Database::transaction(function (PDO $pdo) use ($orderId, $userId, $role, $cleanReason): array {
            $order = Order::findForUpdate($orderId, $pdo);
            if (!$order) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            $status    = (string) $order['status'];
            $payStatus = (string) $order['payment_status'];

            if ($status === 'cancelled') {
                throw new ConflictException('Pesanan ini sudah dibatalkan sebelumnya.');
            }

            if ($status === 'completed') {
                throw new ConflictException('Pesanan yang sudah selesai tidak dapat dibatalkan (BR-10).');
            }

            if ($status === 'pending' && $payStatus === 'unpaid') {
                if (!in_array($role, ['cashier', 'admin'], true)) {
                    throw new ForbiddenException('Akses ditolak. Anda tidak berhak membatalkan pesanan ini.');
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

            if ($role !== 'admin') {
                throw new ForbiddenException('Hanya admin yang memiliki wewenang untuk membatalkan pesanan yang sudah dibayar (BR-10).');
            }

            if (mb_strlen($cleanReason) < 5) {
                throw new ValidationException('Alasan pembatalan pesanan yang sudah dibayar wajib diisi minimal 5 karakter.', [
                    'reason' => 'Alasan pembatalan minimal 5 karakter.',
                ]);
            }

            PaymentService::void($orderId, $userId, $cleanReason, $pdo);
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
