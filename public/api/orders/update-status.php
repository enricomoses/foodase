<?php
declare(strict_types=1);

/**
 * POST api/orders/update-status.php
 * Perbarui alur status pesanan (FR-STS-01, BR-06).
 * Akses: Kasir dan Admin.
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

(new OrderController())->updateStatus();
