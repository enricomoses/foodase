<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;

/**
 * Service untuk menyiapkan data struk cetak (FR-PAY-05).
 */
final class ReceiptService
{
    /**
     * Cari data struk berdasarkan kode pembayaran (PAY-...) atau kode pesanan (FE-...).
     *
     * @return array{order: array<string, mixed>, payment: array<string, mixed>, items: array<int, array<string, mixed>>, cafeName: string}
     */
    public static function resolveByCode(string $code): array
    {
        $cleanCode = trim($code);
        if ($cleanCode === '') {
            throw new ValidationException('Kode pesanan atau pembayaran tidak valid.');
        }

        $order   = null;
        $payment = null;

        if (str_starts_with($cleanCode, 'PAY-')) {
            $payment = Payment::findByCode($cleanCode);
            if ($payment !== null) {
                $order = Order::find((int) $payment['order_id']);
            }
        }

        if ($order === null) {
            $order = Order::findByCode($cleanCode);
            if ($order !== null) {
                $payment = Payment::findByOrderId((int) $order['id']);
            }
        }

        if ($order === null && $payment === null) {
            throw new NotFoundException('Data pesanan atau pembayaran tidak ditemukan.');
        }

        if ($payment === null && $order !== null) {
            $payment = [
                'payment_code'  => '—',
                'method'        => '',
                'total'         => (int) $order['total'],
                'amount_paid'   => 0,
                'change_amount' => 0,
                'paid_at'       => $order['updated_at'] ?? $order['created_at'],
                'cashier_name'  => '—',
            ];
        }

        $orderId = (int) ($order['id'] ?? ($payment['order_id'] ?? 0));
        $items   = $orderId > 0 ? OrderDetail::byOrder($orderId) : [];

        return [
            'order'    => $order ?? [],
            'payment'  => $payment ?? [],
            'items'    => $items,
            'cafeName' => (string) Config::get('app.name', 'FoodEase'),
        ];
    }
}
