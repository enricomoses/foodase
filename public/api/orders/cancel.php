<?php
declare(strict_types=1);

/**
 * POST api/orders/cancel.php
 * Batalkan pesanan (FR-STS-02, FR-STS-03, BR-10).
 * Akses: Kasir (unpaid pending) dan Admin.
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

if (!Request::isPost()) {
    Response::error('Method not allowed.', 405);
}

Csrf::guard();

(new OrderController())->cancel();
