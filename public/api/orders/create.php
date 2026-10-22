<?php
declare(strict_types=1);

/**
 * POST api/orders/create.php — Checkout pelanggan (publik, CSRF required).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\OrderController;

if (!Request::isPost()) {
    Response::error('Method not allowed.', 405);
}

Csrf::guard();

(new OrderController())->create();
