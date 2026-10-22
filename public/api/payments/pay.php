<?php
declare(strict_types=1);

/**
 * POST api/payments/pay.php — Proses pembayaran (kasir/admin, CSRF required).
 * FR-PAY-04, BR-07..09, BR-16.
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\PaymentController;

if (!Request::isPost()) {
    Response::error('Method not allowed.', 405);
}

Csrf::guard();

(new PaymentController())->pay();
