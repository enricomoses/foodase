<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Exceptions\AppException;
use App\Core\Request;
use App\Core\View;
use App\Services\ReceiptService;

/**
 * Controller halaman cetak struk kasir (FR-PAY-05).
 */
final class ReceiptController extends BaseController
{
    public function show(): void
    {
        Auth::requireRole(['cashier', 'admin']);

        $code = trim((string) Request::get('code', ''));

        try {
            $data = ReceiptService::resolveByCode($code);
        } catch (AppException $e) {
            http_response_code($e->statusCode());
            echo View::render('receipt/error', [
                'title'   => 'Struk Tidak Tersedia',
                'message' => $e->getMessage(),
            ], 'print');
            return;
        }

        echo View::render('receipt/receipt', [
            'title'    => 'Struk ' . ($data['order']['order_code'] ?? 'Pesanan'),
            'order'    => $data['order'],
            'payment'  => $data['payment'],
            'items'    => $data['items'],
            'cafeName' => $data['cafeName'],
        ], 'print');
    }
}
