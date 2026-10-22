<?php
declare(strict_types=1);

/**
 * POST api/payments/void.php
 * Void pembayaran pesanan (FR-STS-03, BR-10).
 * Akses: Hanya Admin.
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\PaymentController;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

if (!Request::isPost()) {
    Response::error('Method not allowed.', 405);
}

Csrf::guard();

(new PaymentController())->void();
