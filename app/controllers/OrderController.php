<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use App\Services\UploadService;

/**
 * OrderController — Endpoint checkout dan pengelolaan pesanan.
 */
final class OrderController
{
    /**
     * POST api/orders/create.php
     * Proses checkout pelanggan online (publik, CSRF required).
     *
     * Payload JSON:
     * {
     *   "customer_name": "Dina",
     *   "order_type": "dine_in",
     *   "table_number": 7,       // wajib jika dine_in
     *   "notes": "...",          // opsional
     *   "items": [{ "menu_id": 3, "qty": 2 }]
     * }
     *
     * Response sukses 201:
     * { "success": true, "message": "Pesanan dibuat.", "data": { "order_code": "FE-261022-001", "total": 50000 } }
     */
    public function create(): void
    {
        $input = Request::json();

        try {
            $result = OrderService::create(
                input: $input,
                createdBy: null,
                source: 'online'
            );
        } catch (\InvalidArgumentException $e) {
            // Decode JSON error dari service
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded)) {
                Response::error(
                    $decoded['message'] ?? 'Data tidak valid.',
                    422,
                    $decoded['errors'] ?? []
                );
            }
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan saat membuat pesanan.', 500);
        }

        // Simpan kode pesanan ke session (daftar "pesanan saya" — FR-TRK-03)
        $myCodes = Session::get('my_order_codes', []);
        if (!is_array($myCodes)) {
            $myCodes = [];
        }
        // Simpan 20 kode terakhir
        array_unshift($myCodes, $result['order_code']);
        $myCodes = array_slice($myCodes, 0, 20);
        Session::set('my_order_codes', $myCodes);

        Response::json(
            [
                'order_code' => $result['order_code'],
                'total'      => $result['total'],
            ],
            'Pesanan berhasil dibuat.',
            201
        );
    }

    /**
     * GET api/orders/track.php?code=FE-YYMMDD-NNN&name=...
     * Pelacakan pesanan publik (FR-TRK-01, FR-TRK-04, BR-17).
     */
    public function track(): void
    {
        $code = trim((string) Request::get('code', ''));
        $name = trim((string) Request::get('name', ''));

        if ($code === '') {
            Response::error('Kode pesanan wajib diisi.', 422);
        }

        $order = Order::findByCode($code);
        if (!$order) {
            Response::error('Pesanan tidak ditemukan.', 404);
        }

        // Cek otorisasi pelanggan (BR-17)
        $myCodes = Session::get('my_order_codes', []);
        if (!is_array($myCodes)) {
            $myCodes = [];
        }

        $inSession = in_array($order['order_code'], $myCodes, true);
        if (!$inSession) {
            // Jika tidak ada di session, wajib ada nama dan cocok (case-insensitive)
            if ($name === '' || mb_strtolower($name) !== mb_strtolower((string) $order['customer_name'])) {
                Response::error('Pesanan tidak ditemukan.', 404);
            }
        }

        $details = OrderDetail::byOrder((int) $order['id']);
        $items = array_map(static function (array $d): array {
            return [
                'id'         => (int) $d['id'],
                'menu_id'    => (int) $d['menu_id'],
                'menu_name'  => (string) $d['menu_name'],
                'price'      => (int) $d['price'],
                'qty'        => (int) $d['qty'],
                'subtotal'   => (int) $d['subtotal'],
                'image_url'  => UploadService::imageUrl($d['image'] ?? null),
            ];
        }, $details);

        Response::json([
            'order_code'     => (string) $order['order_code'],
            'customer_name'  => (string) $order['customer_name'],
            'order_type'     => (string) $order['order_type'],
            'table_number'   => $order['table_number'] !== null ? (int) $order['table_number'] : null,
            'notes'          => $order['notes'] !== null ? (string) $order['notes'] : null,
            'status'         => (string) $order['status'],
            'payment_status' => (string) $order['payment_status'],
            'total'          => (int) $order['total'],
            'cancel_reason'  => $order['cancel_reason'] !== null ? (string) $order['cancel_reason'] : null,
            'created_at'     => (string) $order['created_at'],
            'updated_at'     => (string) $order['updated_at'],
            'items'          => $items,
        ], 'Detail status pesanan berhasil diambil.');
    }

    /**
     * GET api/orders/mine.php
     * Mengambil daftar pesanan pelanggan dari session browser (FR-TRK-03).
     */
    public function mine(): void
    {
        $myCodes = Session::get('my_order_codes', []);
        if (!is_array($myCodes) || empty($myCodes)) {
            Response::json([], 'Belum ada riwayat pesanan.');
        }

        $orders = Order::findManyByCodes($myCodes);
        if (empty($orders)) {
            Response::json([], 'Belum ada riwayat pesanan.');
        }

        // Urutkan sesuai urutan dalam session ($myCodes adalah newest first)
        $orderMap = [];
        foreach ($orders as $row) {
            $orderMap[$row['order_code']] = $row;
        }

        $sorted = [];
        foreach ($myCodes as $code) {
            if (isset($orderMap[$code])) {
                $order = $orderMap[$code];
                $details = OrderDetail::byOrder((int) $order['id']);

                $items = array_map(static function (array $d): array {
                    return [
                        'menu_name' => (string) $d['menu_name'],
                        'price'     => (int) $d['price'],
                        'qty'       => (int) $d['qty'],
                        'subtotal'  => (int) $d['subtotal'],
                        'image_url' => UploadService::imageUrl($d['image'] ?? null),
                    ];
                }, $details);

                $sorted[] = [
                    'order_code'     => (string) $order['order_code'],
                    'customer_name'  => (string) $order['customer_name'],
                    'order_type'     => (string) $order['order_type'],
                    'table_number'   => $order['table_number'] !== null ? (int) $order['table_number'] : null,
                    'status'         => (string) $order['status'],
                    'payment_status' => (string) $order['payment_status'],
                    'total'          => (int) $order['total'],
                    'created_at'     => (string) $order['created_at'],
                    'items_count'    => array_sum(array_column($items, 'qty')),
                    'items'          => $items,
                ];
            }
        }

        Response::json($sorted, 'Daftar riwayat pesanan berhasil diambil.');
    }
}
