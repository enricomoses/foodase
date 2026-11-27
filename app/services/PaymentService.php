<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Models\Order;
use App\Models\Payment;
use PDO;
use PDOException;

/**
 * PaymentService — logika bisnis pembayaran atomik dan void (BR-07, BR-08, BR-09, BR-10, BR-16).
 */
final class PaymentService
{
    /**
     * Proses pembayaran pesanan dalam satu transaksi atomik.
     *
     * @return array{payment_code: string, payment_id: int, total: int, amount_paid: int, change_amount: int, method: string}
     */
    public static function pay(
        int $orderId,
        string $method,
        int $amountPaid,
        int $cashierId
    ): array {
        if (!in_array($method, ['cash', 'qris'], true)) {
            throw new ValidationException('Metode pembayaran tidak valid. Pilih cash atau qris.', [
                'method' => 'Metode tidak valid.',
            ]);
        }

        return Database::transaction(function (PDO $pdo) use ($orderId, $method, $amountPaid, $cashierId): array {
            $order = Order::findForUpdate($orderId, $pdo);
            if (!$order) {
                throw new NotFoundException('Pesanan tidak ditemukan.');
            }

            if ($order['status'] !== 'pending' || $order['payment_status'] !== 'unpaid') {
                throw new ConflictException('Pesanan ini sudah dibayar, dibatalkan, atau tidak dalam status yang dapat diproses.');
            }

            $total = (int) $order['total'];

            if ($method === 'qris') {
                $amountPaid   = $total;
                $changeAmount = 0;
            } else {
                if ($amountPaid < $total) {
                    throw new ValidationException('Nominal yang dibayarkan kurang dari total pesanan.', [
                        'amount_paid' => 'Nominal kurang dari total ' . rupiah($total),
                    ]);
                }
                $changeAmount = $amountPaid - $total;
            }

            $maxRetries  = 3;
            $paymentCode = null;
            $paymentId   = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    $paymentCode = Payment::generateCode($pdo);
                    $paymentId   = Payment::create([
                        'payment_code'  => $paymentCode,
                        'order_id'      => $orderId,
                        'method'        => $method,
                        'total'         => $total,
                        'amount_paid'   => $amountPaid,
                        'change_amount' => $changeAmount,
                        'cashier_id'    => $cashierId,
                    ], $pdo);
                    break;
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        if (str_contains($e->getMessage(), 'order_id') || str_contains($e->getMessage(), 'uq_payments_order')) {
                            throw new ConflictException('Pesanan ini sudah memiliki pembayaran.');
                        }
                        if ($attempt < $maxRetries - 1) {
                            continue;
                        }
                    }
                    throw $e;
                }
            }

            if ($paymentCode === null || $paymentId === null) {
                throw new AppException('Gagal membuat kode pembayaran unik setelah beberapa percobaan.', 500);
            }

            Order::markPaid($orderId, $pdo);

            return [
                'payment_code'  => $paymentCode,
                'payment_id'    => $paymentId,
                'total'         => $total,
                'amount_paid'   => $amountPaid,
                'change_amount' => $changeAmount,
                'method'        => $method,
            ];
        });
    }

    /**
     * Void pembayaran pesanan (BR-10).
     */
    public static function void(
        int $orderId,
        int $userId,
        string $reason,
        ?PDO $pdo = null
    ): array {
        $cleanReason = trim($reason);
        if (mb_strlen($cleanReason) < 5) {
            throw new ValidationException('Alasan pembatalan/void wajib diisi minimal 5 karakter.', [
                'reason' => 'Alasan minimal 5 karakter.',
            ]);
        }

        $execute = static function (PDO $activePdo) use ($orderId, $userId, $cleanReason): array {
            $payment = Payment::findByOrderIdForUpdate($orderId, $activePdo);
            if (!$payment) {
                throw new NotFoundException('Data pembayaran untuk pesanan ini tidak ditemukan.');
            }

            if ($payment['status'] === 'void') {
                throw new ConflictException('Pembayaran untuk pesanan ini sudah pernah di-void.');
            }

            Payment::void($orderId, $userId, $cleanReason, $activePdo);

            return [
                'id'           => (int) $payment['id'],
                'payment_code' => (string) $payment['payment_code'],
                'order_id'     => $orderId,
                'status'       => 'void',
                'void_reason'  => $cleanReason,
                'voided_by'    => $userId,
            ];
        };

        return $pdo !== null ? $execute($pdo) : Database::transaction($execute);
    }
}
