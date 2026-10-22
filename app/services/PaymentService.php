<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\Payment;
use PDOException;

/**
 * PaymentService — logika bisnis pembayaran.
 *
 * Dipakai oleh:
 * - api/payments/pay.php   (kasir/admin)
 *
 * Aturan Bisnis:
 * - BR-07: Pembayaran sukses → payment_status=paid, status=processing, dalam satu transaksi.
 * - BR-08: Tunai: amount_paid >= total; QRIS: amount_paid = total, change = 0.
 * - BR-09: Double-payment dicegah dengan SELECT ... FOR UPDATE + UNIQUE order_id di payments.
 * - BR-16: payment_code PAY-YYMMDD-NNN dibuat di dalam transaksi.
 */
final class PaymentService
{
    /**
     * Proses pembayaran pesanan.
     *
     * @param int    $orderId    ID pesanan dari tabel orders.
     * @param string $method     'cash' | 'qris'
     * @param int    $amountPaid Nominal yang dibayarkan (Rupiah, integer).
     *                           Untuk QRIS, nilai ini diabaikan dan dipaksa = total.
     * @param int    $cashierId  ID user kasir yang memproses.
     *
     * @return array  ['payment_code' => string, 'total' => int, 'amount_paid' => int, 'change_amount' => int]
     *
     * @throws \InvalidArgumentException  Validasi gagal (pesan + errors array di message JSON).
     * @throws \RuntimeException          Status tidak sesuai (409) atau error DB.
     */
    public static function pay(
        int $orderId,
        string $method,
        int $amountPaid,
        int $cashierId
    ): array {
        /* ── Validasi method ─────────────────────────────────────────── */
        if (!in_array($method, ['cash', 'qris'], true)) {
            throw new \InvalidArgumentException(json_encode([
                'message' => 'Metode pembayaran tidak valid. Pilih cash atau qris.',
                'errors'  => ['method' => 'Metode tidak valid.'],
            ]));
        }

        return Database::transaction(function (\PDO $pdo) use (
            $orderId,
            $method,
            $amountPaid,
            $cashierId
        ): array {
            /* ── Kunci baris pesanan agar tidak double-submit (BR-09) ─── */
            $stmt = $pdo->prepare(
                'SELECT id, status, payment_status, total FROM orders WHERE id = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan tidak ditemukan.',
                    'status'  => 404,
                ]));
            }

            /* ── Tolak bila bukan pending/unpaid (BR-09) ─────────────── */
            if ($order['status'] !== 'pending' || $order['payment_status'] !== 'unpaid') {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pesanan ini sudah dibayar, dibatalkan, atau tidak dalam status yang dapat diproses.',
                    'status'  => 409,
                ]));
            }

            $total = (int) $order['total'];

            /* ── Hitung nominal dan kembalian (BR-08) ────────────────── */
            if ($method === 'qris') {
                // QRIS: amount_paid dipaksa = total, change = 0
                $amountPaid   = $total;
                $changeAmount = 0;
            } else {
                // Tunai: validasi kurang bayar
                if ($amountPaid < $total) {
                    throw new \InvalidArgumentException(json_encode([
                        'message' => 'Nominal yang dibayarkan kurang dari total pesanan.',
                        'errors'  => ['amount_paid' => 'Nominal kurang dari total Rp ' . number_format($total, 0, ',', '.')],
                    ]));
                }
                $changeAmount = $amountPaid - $total;
            }

            /* ── Buat payment_code PAY-YYMMDD-NNN (BR-16) ───────────── */
            $maxRetries  = 3;
            $paymentCode = null;
            $paymentId   = null;

            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                try {
                    $paymentCode = Payment::generateCode($pdo);

                    $paymentId = Payment::create([
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
                    // Duplicate key pada payment_code atau order_id → coba lagi (atau tolak double-submit)
                    if ($e->getCode() === '23000') {
                        // Jika sudah ada payment untuk order ini, tolak (unique order_id)
                        if (str_contains($e->getMessage(), 'order_id')) {
                            throw new \RuntimeException(json_encode([
                                'message' => 'Pesanan ini sudah memiliki pembayaran.',
                                'status'  => 409,
                            ]));
                        }
                        // Duplicate payment_code → coba ulang
                        if ($attempt < $maxRetries - 1) {
                            continue;
                        }
                    }
                    throw $e;
                }
            }

            if ($paymentCode === null || $paymentId === null) {
                throw new \RuntimeException('Gagal membuat kode pembayaran unik setelah beberapa percobaan.');
            }

            /* ── Update orders: payment_status=paid, status=processing (BR-07) ── */
            $updateStmt = $pdo->prepare(
                'UPDATE orders SET payment_status = \'paid\', status = \'processing\', updated_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([':id' => $orderId]);

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
     * Void pembayaran pesanan (FR-STS-03, BR-10).
     *
     * @param int       $orderId
     * @param int       $userId  ID admin yang memproses void
     * @param string    $reason  Alasan pembatalan/void (min 5 karakter)
     * @param \PDO|null $pdo     Instance PDO jika sudah di dalam transaksi
     *
     * @return array    Data payment yang di-void
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function void(
        int $orderId,
        int $userId,
        string $reason,
        ?\PDO $pdo = null
    ): array {
        $cleanReason = trim($reason);
        if (mb_strlen($cleanReason) < 5) {
            throw new \InvalidArgumentException(json_encode([
                'message' => 'Alasan pembatalan/void wajib diisi minimal 5 karakter.',
                'errors'  => ['reason' => 'Alasan minimal 5 karakter.'],
            ]));
        }

        $execute = function (\PDO $activePdo) use ($orderId, $userId, $cleanReason): array {
            $stmt = $activePdo->prepare(
                'SELECT * FROM payments WHERE order_id = :order_id FOR UPDATE'
            );
            $stmt->execute([':order_id' => $orderId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new \RuntimeException(json_encode([
                    'message' => 'Data pembayaran untuk pesanan ini tidak ditemukan.',
                    'status'  => 404,
                ]));
            }

            if ($payment['status'] === 'void') {
                throw new \RuntimeException(json_encode([
                    'message' => 'Pembayaran untuk pesanan ini sudah pernah di-void.',
                    'status'  => 409,
                ]));
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

        if ($pdo !== null) {
            return $execute($pdo);
        }

        return Database::transaction($execute);
    }
}
