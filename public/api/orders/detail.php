<?php
declare(strict_types=1);

/**
 * GET api/orders/detail.php?id=... atau ?code=...
 * Detail pesanan untuk kasir/admin (FR-COR-04).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;
use App\Core\Request;
use App\Core\Response;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new OrderController())->detail();
