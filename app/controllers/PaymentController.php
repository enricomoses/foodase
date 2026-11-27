<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Payment;
use App\Services\PaymentService;

/**
 * PaymentController — Endpoint pembayaran, riwayat transaksi, dan void.
 */
final class PaymentController extends BaseController
{
    public function pay(): void
    {
        $this->handleApi(function (): void {
            $input      = Request::body();
            $orderId    = (int) ($input['order_id'] ?? 0);
            $method     = trim((string) ($input['method'] ?? ''));
            $amountPaid = (int) ($input['amount_paid'] ?? 0);

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
                throw new ValidationException('Data tidak valid.', $errors);
            }

            $result = PaymentService::pay(
                orderId: $orderId,
                method: $method,
                amountPaid: $amountPaid,
                cashierId: (int) (Auth::id() ?? 0)
            );

            Response::json([
                'payment_code'  => $result['payment_code'],
                'total'         => $result['total'],
                'amount_paid'   => $result['amount_paid'],
                'change_amount' => $result['change_amount'],
                'method'        => $result['method'],
            ], 'Pembayaran berhasil diproses.');
        }, method: 'POST', roles: ['cashier', 'admin'], csrf: true);
    }

    /**
     * Daftar riwayat transaksi (semua status atau filter paid/void).
     * Scope kasir dipaksa di server (kasir hanya miliknya, admin semua).
     */
    public function history(): void
    {
        $this->handleApi(function (): void {
            $user   = Auth::user();
            $role   = (string) ($user['role'] ?? 'cashier');
            $status = (string) Request::get('status', '');

            $filters = [
                'q'         => trim((string) Request::get('q', '')),
                'status'    => in_array($status, ['paid', 'void'], true) ? $status : '',
                'method'    => Request::get('method'),
                'date_from' => Request::get('date_from'),
                'date_to'   => Request::get('date_to'),
                'page'      => (int) Request::get('page', 1),
                'per_page'  => (int) Request::get('per_page', 20),
            ];

            if ($role === 'cashier') {
                $filters['cashier_id'] = (int) ($user['id'] ?? 0);
            }

            $result = Payment::list($filters);

            foreach ($result['data'] as &$row) {
                $row['total']         = (int) $row['total'];
                $row['amount_paid']   = (int) $row['amount_paid'];
                $row['change_amount'] = (int) $row['change_amount'];
            }
            unset($row);

            Response::json([
                'payments'   => $result['data'],
                'paid_total' => $result['paid_total'],
                'pagination' => [
                    'total'    => $result['total'],
                    'page'     => $result['page'],
                    'per_page' => $result['per_page'],
                    'pages'    => $result['pages'],
                ],
                'is_admin'   => $role === 'admin',
            ], 'Riwayat transaksi berhasil diambil.');
        }, method: 'GET', roles: ['cashier', 'admin']);
    }

    public function void(): void
    {
        $this->handleApi(function (): void {
            $input   = Request::body();
            $orderId = (int) ($input['order_id'] ?? $input['id'] ?? 0);
            $reason  = trim((string) ($input['reason'] ?? ''));

            $errors = [];
            if ($orderId <= 0) {
                $errors['order_id'] = 'ID pesanan tidak valid.';
            }
            if (mb_strlen($reason) < 5) {
                $errors['reason'] = 'Alasan void wajib diisi minimal 5 karakter.';
            }
            if (!empty($errors)) {
                throw new ValidationException('Data tidak valid.', $errors);
            }

            $result = PaymentService::void($orderId, (int) (Auth::id() ?? 0), $reason);
            Response::json($result, 'Pembayaran berhasil di-void.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }
}
