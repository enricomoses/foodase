<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\I18n;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use App\Services\UploadService;

/**
 * OrderController — Endpoint checkout, pelacakan, dan pengelolaan pesanan.
 */
final class OrderController extends BaseController
{
    public function create(): void
    {
        $this->handleApi(function (): void {
            $result = OrderService::create(
                input: Request::json(),
                createdBy: null,
                source: 'online'
            );

            $myCodes = Session::get('my_order_codes', []);
            if (!is_array($myCodes)) {
                $myCodes = [];
            }
            array_unshift($myCodes, $result['order_code']);
            Session::set('my_order_codes', array_slice(array_values(array_unique($myCodes)), 0, 20));

            Response::json([
                'order_code' => $result['order_code'],
                'total'      => $result['total'],
            ], 'Pesanan berhasil dibuat.', 201);
        }, method: 'POST', csrf: true);
    }

    public function createPos(): void
    {
        $this->handleApi(function (): void {
            $input  = Request::json();
            $userId = Auth::id();

            $result = OrderService::create(
                input: $input,
                createdBy: ($userId !== null && $userId > 0) ? $userId : null,
                source: 'cashier'
            );

            Response::json([
                'order_id'      => $result['order_id'],
                'order_code'    => $result['order_code'],
                'total'         => $result['total'],
                'customer_name' => trim((string) ($input['customer_name'] ?? '')),
                'order_type'    => trim((string) ($input['order_type'] ?? '')),
                'table_number'  => !empty($input['table_number']) ? (int) $input['table_number'] : null,
            ], 'Pesanan kasir berhasil dibuat.', 201);
        }, method: 'POST', roles: ['cashier', 'admin'], csrf: true);
    }

    public function track(): void
    {
        $this->handleApi(function (): void {
            $code = trim((string) Request::get('code', ''));
            $name = trim((string) Request::get('name', ''));

            if ($code === '') {
                throw new ValidationException('Kode pesanan wajib diisi.');
            }

            $order = Order::findByCode($code);
            if (!$order) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            // BR-17: Cek kepemilikan via session atau kecocokan nama pemesan
            $myCodes   = Session::get('my_order_codes', []);
            $inSession = is_array($myCodes) && in_array($order['order_code'], $myCodes, true);
            if (!$inSession && ($name === '' || mb_strtolower($name) !== mb_strtolower((string) $order['customer_name']))) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            $items = $this->formatItems(OrderDetail::byOrder((int) $order['id']), true);

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
        }, method: 'GET');
    }

    public function mine(): void
    {
        $this->handleApi(function (): void {
            $myCodes = Session::get('my_order_codes', []);
            if (!is_array($myCodes) || empty($myCodes)) {
                Response::json([], 'Belum ada riwayat pesanan.');
            }

            $orders = Order::findManyByCodes($myCodes);
            if (empty($orders)) {
                Response::json([], 'Belum ada riwayat pesanan.');
            }

            // Batch fetch seluruh order_details dalam 1 query (menghilangkan N+1)
            $orderIds     = array_map(static fn(array $o): int => (int) $o['id'], $orders);
            $detailsById  = OrderDetail::byOrderIds($orderIds);

            $orderMap = [];
            foreach ($orders as $row) {
                $orderMap[$row['order_code']] = $row;
            }

            $sorted = [];
            foreach ($myCodes as $code) {
                if (!isset($orderMap[$code])) {
                    continue;
                }
                $order   = $orderMap[$code];
                $details = $detailsById[(int) $order['id']] ?? [];
                $items   = $this->formatItems($details, false);

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

            Response::json($sorted, 'Daftar riwayat pesanan berhasil diambil.');
        }, method: 'GET');
    }

    public function list(): void
    {
        $this->handleApi(function (): void {
            $filters = [
                'status'     => Request::get('status'),
                'q'          => trim((string) Request::get('q', '')),
                'order_type' => Request::get('type'),
                'date'       => Request::get('date'),
                'page'       => (int) Request::get('page', 1),
                'per_page'   => (int) Request::get('per_page', 20),
            ];

            $result = Order::list($filters);

            // Batch fetch order_details dalam 1 query (menghilangkan N+1 pada polling 8 detik!)
            $orderIds    = array_map(static fn(array $r): int => (int) $r['id'], $result['data']);
            $detailsById = OrderDetail::byOrderIds($orderIds);

            foreach ($result['data'] as &$row) {
                $details              = $detailsById[(int) $row['id']] ?? [];
                $row['total']         = (int) $row['total'];
                $row['table_number']  = $row['table_number'] !== null ? (int) $row['table_number'] : null;
                $row['items_count']   = array_sum(array_column($details, 'qty'));
                $row['items_summary'] = implode(', ', array_map(
                    static fn(array $d): string => $d['menu_name'] . ' × ' . $d['qty'],
                    $details
                ));
            }
            unset($row);

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
        }, method: 'GET', roles: ['cashier', 'admin']);
    }

    public function detail(): void
    {
        $this->handleApi(function (): void {
            $id   = (int) Request::get('id', 0);
            $code = trim((string) Request::get('code', ''));

            if ($id > 0) {
                $order = Order::find($id);
            } elseif ($code !== '') {
                $order = Order::findByCode($code);
            } else {
                throw new ValidationException('Parameter id atau code wajib diisi.');
            }

            if (!$order) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            $items = $this->formatItems(OrderDetail::byOrder((int) $order['id']), true);

            Response::json([
                'order' => [
                    'id'                => (int) $order['id'],
                    'order_code'        => (string) $order['order_code'],
                    'customer_name'     => (string) $order['customer_name'],
                    'order_type'        => (string) $order['order_type'],
                    'table_number'      => $order['table_number'] !== null ? (int) $order['table_number'] : null,
                    'notes'             => $order['notes'] !== null ? (string) $order['notes'] : null,
                    'source'            => (string) $order['source'],
                    'status'            => (string) $order['status'],
                    'payment_status'    => (string) $order['payment_status'],
                    'total'             => (int) $order['total'],
                    'cancel_reason'     => $order['cancel_reason'] !== null ? (string) $order['cancel_reason'] : null,
                    'cancelled_by'      => isset($order['cancelled_by']) && $order['cancelled_by'] !== null ? (int) $order['cancelled_by'] : null,
                    'cancelled_by_name' => isset($order['cancelled_by_name']) && $order['cancelled_by_name'] !== null ? (string) $order['cancelled_by_name'] : null,
                    'created_at'        => (string) $order['created_at'],
                    'updated_at'        => (string) $order['updated_at'],
                ],
                'items' => $items,
            ], 'Detail pesanan berhasil diambil.');
        }, method: 'GET', roles: ['cashier', 'admin']);
    }

    public function updateStatus(): void
    {
        $this->handleApi(function (): void {
            $input      = Request::body();
            $orderId    = (int) ($input['order_id'] ?? $input['id'] ?? 0);
            $nextStatus = trim((string) ($input['next_status'] ?? $input['status'] ?? $input['next'] ?? ''));

            if ($orderId <= 0) {
                throw new ValidationException('ID pesanan tidak valid.', ['order_id' => 'ID pesanan wajib diisi.']);
            }
            if ($nextStatus === '') {
                throw new ValidationException('Status tujuan wajib diisi.', ['next_status' => 'Status tujuan wajib diisi.']);
            }

            $result = OrderService::updateStatus($orderId, $nextStatus);
            Response::json($result, 'Status pesanan berhasil diperbarui.');
        }, method: 'POST', roles: ['cashier', 'admin'], csrf: true);
    }

    public function cancel(): void
    {
        $this->handleApi(function (): void {
            $user    = Auth::user();
            $userId  = (int) ($user['id'] ?? 0);
            $role    = (string) ($user['role'] ?? 'cashier');
            $input   = Request::body();
            $orderId = (int) ($input['order_id'] ?? $input['id'] ?? 0);
            $reason  = isset($input['reason']) ? (string) $input['reason'] : null;

            if ($orderId <= 0) {
                throw new ValidationException('ID pesanan tidak valid.', ['order_id' => 'ID pesanan wajib diisi.']);
            }

            $result = OrderService::cancel($orderId, $userId, $role, $reason);
            Response::json($result, 'Pesanan berhasil dibatalkan.');
        }, method: 'POST', roles: ['cashier', 'admin'], csrf: true);
    }

    /**
     * Format daftar item order_details secara konsisten untuk track(), mine(), dan detail().
     *
     * @param array<int, array<string, mixed>> $details
     * @return array<int, array<string, mixed>>
     */
    private function formatItems(array $details, bool $includeIds): array
    {
        return array_map(static function (array $d) use ($includeIds): array {
            $rawName = (string) $d['menu_name'];
            $item    = [
                'menu_name'          => I18n::translateMenuName($rawName),
                'menu_name_original' => $rawName,
                'price'              => (int) $d['price'],
                'qty'                => (int) $d['qty'],
                'subtotal'           => (int) $d['subtotal'],
                'image_url'          => UploadService::imageUrl($d['image'] ?? null),
            ];

            if ($includeIds) {
                $item = ['id' => (int) $d['id'], 'menu_id' => (int) $d['menu_id']] + $item;
            }

            return $item;
        }, $details);
    }
}
