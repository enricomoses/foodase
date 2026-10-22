<?php
declare(strict_types=1);

/**
 * Halaman Cetak Struk Kasir.
 * FR-PAY-05, prd.md §6.11 & §12.4 "Struk".
 *
 * Akses: kasir dan admin.
 * Parameter GET: code (order_code atau payment_code).
 * Layout: print (80mm monospace, tanpa navbar).
 */

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;

Auth::requireRole(['cashier', 'admin']);

$code = trim((string) ($_GET['code'] ?? ''));

if ($code === '') {
    http_response_code(400);
    echo '<p style="font-family:sans-serif;padding:20px;color:#d00;">Kode pesanan atau pembayaran tidak valid.</p>';
    exit;
}

$order = null;
$payment = null;

// Cek apakah kode pembayaran (PAY-) atau kode pesanan (FE-)
if (str_starts_with($code, 'PAY-')) {
    $payment = Payment::findByCode($code);
    if ($payment !== null) {
        $order = Order::find((int) $payment['order_id']);
    }
}

if ($order === null) {
    $order = Order::findByCode($code);
    if ($order !== null) {
        $payment = Payment::findByOrderId((int) $order['id']);
    }
}

if ($order === null && $payment === null) {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;padding:20px;color:#d00;">Data pesanan atau pembayaran tidak ditemukan.</p>';
    exit;
}

// Jika order ditemukan tapi belum ada payment record (misal status unpaid)
if ($payment === null && $order !== null) {
    $payment = [
        'payment_code'  => '—',
        'method'        => '—',
        'total'         => (int) $order['total'],
        'amount_paid'   => 0,
        'change_amount' => 0,
        'paid_at'       => $order['updated_at'] ?? $order['created_at'],
        'cashier_name'  => '—',
    ];
}

$orderId = (int) ($order['id'] ?? ($payment['order_id'] ?? 0));
$items   = $orderId > 0 ? OrderDetail::byOrder($orderId) : [];

echo View::render('receipt/receipt', [
    'title'    => 'Struk ' . ($order['order_code'] ?? 'Pesanan'),
    'order'    => $order ?? [],
    'payment'  => $payment ?? [],
    'items'    => $items,
    'cafeName' => 'FoodEase',
], 'print');
