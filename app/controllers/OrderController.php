<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\OrderService;

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
}
