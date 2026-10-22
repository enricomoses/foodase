<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
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

    /**
     * GET api/orders/list.php
     * Daftar pesanan kasir/admin dengan filter status, q, type, date, pagination (FR-COR-01, FR-COR-02).
     */
    public function list(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $status  = Request::get('status');
        $q       = trim((string) Request::get('q', ''));
        $type    = Request::get('type');
        $date    = Request::get('date');
        $page    = max(1, (int) Request::get('page', 1));
        $perPage = max(1, min(100, (int) Request::get('per_page', 20)));

        $filters = [
            'q'        => $q,
            'page'     => $page,
            'per_page' => $perPage,
        ];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $filters['status'] = $status;
        }

        if ($type !== null && $type !== '' && $type !== 'all') {
            $filters['order_type'] = $type;
        }

        if ($date !== null && $date !== '') {
            $filters['date'] = $date;
        }

        $result = Order::search($filters);

        // Tambahkan ringkasan item & item_count untuk tiap baris
        foreach ($result['data'] as &$row) {
            $details = OrderDetail::byOrder((int) $row['id']);
            $row['total']        = (int) $row['total'];
            $row['table_number'] = $row['table_number'] !== null ? (int) $row['table_number'] : null;
            $row['items_count']  = array_sum(array_column($details, 'qty'));
            $row['items_summary'] = implode(', ', array_map(static function (array $d): string {
                return $d['menu_name'] . ' × ' . $d['qty'];
            }, $details));
        }
        unset($row);

        // Ambil jumlah per tab status kasir
        $tabCounts = Order::tabCounts(['date' => $filters['date'] ?? null]);

        Response::json([
            'orders'     => $result['data'],
            'pagination' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
                'pages'    => $result['pages'],
            ],
            'tab_counts' => $tabCounts,
        ], 'Daftar pesanan berhasil diambil.');
    }

    /**
     * GET api/orders/detail.php?id=... atau ?code=...
     * Detail pesanan untuk modal kasir/admin (FR-COR-04).
     */
    public function detail(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $id   = (int) Request::get('id', 0);
        $code = trim((string) Request::get('code', ''));

        if ($id > 0) {
            $order = Order::find($id);
        } elseif ($code !== '') {
            $order = Order::findByCode($code);
        } else {
            Response::error('Parameter id atau code wajib diisi.', 422);
        }

        if (!$order) {
            Response::error('Pesanan tidak ditemukan.', 404);
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
            'order' => [
                'id'             => (int) $order['id'],
                'order_code'     => (string) $order['order_code'],
                'customer_name'  => (string) $order['customer_name'],
                'order_type'     => (string) $order['order_type'],
                'table_number'   => $order['table_number'] !== null ? (int) $order['table_number'] : null,
                'notes'          => $order['notes'] !== null ? (string) $order['notes'] : null,
                'source'         => (string) $order['source'],
                'status'         => (string) $order['status'],
                'payment_status' => (string) $order['payment_status'],
                'total'          => (int) $order['total'],
                'cancel_reason'     => $order['cancel_reason'] !== null ? (string) $order['cancel_reason'] : null,
                'cancelled_by'      => isset($order['cancelled_by']) && $order['cancelled_by'] !== null ? (int) $order['cancelled_by'] : null,
                'cancelled_by_name' => isset($order['cancelled_by_name']) && $order['cancelled_by_name'] !== null ? (string) $order['cancelled_by_name'] : null,
                'created_at'        => (string) $order['created_at'],
                'updated_at'        => (string) $order['updated_at'],
            ],
            'items' => $items,
        ], 'Detail pesanan berhasil diambil.');
    }

    /**
     * POST api/orders/update-status.php
     * Perbarui status pesanan (FR-STS-01, BR-06).
     * Body JSON: { "order_id": int, "next_status": string }
     */
    public function updateStatus(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $input = Request::json();
        $orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;
        if ($orderId <= 0 && isset($input['id'])) {
            $orderId = (int) $input['id'];
        }
        $nextStatus = trim((string) ($input['next_status'] ?? $input['status'] ?? $input['next'] ?? ''));

        if ($orderId <= 0) {
            Response::error('ID pesanan tidak valid.', 422, ['order_id' => 'ID pesanan wajib diisi.']);
        }

        if ($nextStatus === '') {
            Response::error('Status tujuan wajib diisi.', 422, ['next_status' => 'Status tujuan wajib diisi.']);
        }

        try {
            $result = OrderService::updateStatus($orderId, $nextStatus);
            Response::json($result, 'Status pesanan berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded)) {
                $status = isset($decoded['status']) ? (int) $decoded['status'] : 409;
                Response::error($decoded['message'] ?? 'Konflik status.', $status);
            }
            Response::error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan saat memperbarui status pesanan.', 500);
        }
    }

    /**
     * POST api/orders/cancel.php
     * Batalkan pesanan (FR-STS-02, FR-STS-03, BR-10).
     * Body JSON: { "order_id": int, "reason": ?string }
     */
    public function cancel(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);
        $role = (string) ($user['role'] ?? 'cashier');

        $input = Request::json();
        $orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;
        if ($orderId <= 0 && isset($input['id'])) {
            $orderId = (int) $input['id'];
        }
        $reason = isset($input['reason']) ? (string) $input['reason'] : null;

        if ($orderId <= 0) {
            Response::error('ID pesanan tidak valid.', 422, ['order_id' => 'ID pesanan wajib diisi.']);
        }

        try {
            $result = OrderService::cancel($orderId, $userId, $role, $reason);
            Response::json($result, 'Pesanan berhasil dibatalkan.');
        } catch (\InvalidArgumentException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded)) {
                Response::error($decoded['message'] ?? 'Data tidak valid.', 422, $decoded['errors'] ?? []);
            }
            Response::error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $decoded = json_decode($e->getMessage(), true);
            if (is_array($decoded)) {
                $status = isset($decoded['status']) ? (int) $decoded['status'] : 409;
                Response::error($decoded['message'] ?? 'Gagal membatalkan pesanan.', $status);
            }
            Response::error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan saat membatalkan pesanan.', 500);
        }
    }
}
