<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Services\PaymentService;

/**
 * PaymentController — Endpoint pembayaran dan riwayat transaksi.
 */
final class PaymentController
{
    /**
     * POST api/payments/pay.php
     * Proses pembayaran pesanan oleh kasir/admin (FR-PAY-04, BR-07..09).
     *
     * Payload JSON:
     * {
     *   "order_id": 1,
     *   "method": "cash" | "qris",
     *   "amount_paid": 50000
     * }
     *
     * Response sukses 200:
     * {
     *   "success": true,
     *   "message": "...",
     *   "data": { "payment_code": "PAY-261022-001", "total": 45000, "amount_paid": 50000, "change_amount": 5000, "method": "cash" }
     * }
     */
    public function pay(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $user  = Auth::user();
        $input = Request::json();

        $orderId    = isset($input['order_id']) ? (int) $input['order_id'] : 0;
        $method     = trim((string) ($input['method'] ?? ''));
        $amountPaid = isset($input['amount_paid']) ? (int) $input['amount_paid'] : 0;

        // Validasi dasar input
        $errors = [];
        if ($orderId <= 0) {
            $errors['order_id'] = 'ID pesanan tidak valid.';
        }
        if (!in_array($method, ['cash', 'qris'], true)) {
            $errors['method'] = 'Metode pembayaran tidak valid.';
        }
        if ($method === 'cash' && $amountPaid <= 0) {
            $errors['amount_paid'] = 'Nominal bayar harus lebih dari 0.';
        }

        if (!empty($errors)) {
            Response::error('Data tidak valid.', 422, $errors);
        }

        try {
            $result = PaymentService::pay(
                orderId:    $orderId,
                method:     $method,
                amountPaid: $amountPaid,
                cashierId:  (int) ($user['id'] ?? 0),
            );
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
                Response::error($decoded['message'] ?? 'Konflik status.', $status);
            }
            log_error($e);
            Response::error('Terjadi kesalahan saat memproses pembayaran.', 500);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan saat memproses pembayaran.', 500);
        }

        Response::json([
            'payment_code'  => $result['payment_code'],
            'total'         => $result['total'],
            'amount_paid'   => $result['amount_paid'],
            'change_amount' => $result['change_amount'],
            'method'        => $result['method'],
        ], 'Pembayaran berhasil diproses.');
    }

    /**
     * GET api/payments/list.php
     * Daftar riwayat pembayaran (FR-HIS-01..02).
     * Kasir hanya melihat miliknya; admin melihat semua.
     */
    public function list(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $user = Auth::user();
        $role = (string) ($user['role'] ?? 'cashier');

        $method   = Request::get('method');
        $q        = trim((string) Request::get('q', ''));
        $dateFrom = Request::get('date_from');
        $dateTo   = Request::get('date_to');
        $page     = max(1, (int) Request::get('page', 1));
        $perPage  = max(1, min(100, (int) Request::get('per_page', 20)));

        $filters = [
            'q'        => $q,
            'page'     => $page,
            'per_page' => $perPage,
        ];

        // Kasir hanya melihat pembayaran yang dilakukan olehnya
        if ($role === 'cashier') {
            $filters['cashier_id'] = (int) ($user['id'] ?? 0);
        }

        if ($method !== null && $method !== '') {
            $filters['method'] = $method;
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }

        if ($dateTo !== null && $dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }

        $result = Payment::list($filters);

        // Format nilai agar konsisten
        foreach ($result['data'] as &$row) {
            $row['total']         = (int) $row['total'];
            $row['amount_paid']   = (int) $row['amount_paid'];
            $row['change_amount'] = (int) $row['change_amount'];
        }
        unset($row);

        Response::json([
            'payments'   => $result['data'],
            'pagination' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
                'pages'    => $result['pages'],
            ],
        ], 'Riwayat pembayaran berhasil diambil.');
    }

    /**
     * POST api/payments/void.php
     * Void pembayaran (FR-STS-03, BR-10). Hanya boleh dilakukan oleh admin.
     * Body JSON: { "order_id": int, "reason": string }
     */
    public function void(): void
    {
        Auth::requireRole(['admin']);

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);

        $input = Request::json();
        $orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;
        if ($orderId <= 0 && isset($input['id'])) {
            $orderId = (int) $input['id'];
        }
        $reason = trim((string) ($input['reason'] ?? ''));

        $errors = [];
        if ($orderId <= 0) {
            $errors['order_id'] = 'ID pesanan tidak valid.';
        }
        if (mb_strlen($reason) < 5) {
            $errors['reason'] = 'Alasan void wajib diisi minimal 5 karakter.';
        }

        if (!empty($errors)) {
            Response::error('Data tidak valid.', 422, $errors);
        }

        try {
            $result = PaymentService::void($orderId, $userId, $reason);
            Response::json($result, 'Pembayaran berhasil di-void.');
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
                Response::error($decoded['message'] ?? 'Gagal melakukan void.', $status);
            }
            Response::error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan saat memproses void pembayaran.', 500);
        }
    }
}
