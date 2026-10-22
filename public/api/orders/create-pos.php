<?php
declare(strict_types=1);

/**
 * POST api/orders/create-pos.php — Pembuatan pesanan langsung oleh kasir (POS) (kasir/admin, CSRF required).
 * FR-POS-01..03, prd.md §6.10, §4.3.
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\OrderController;

if (!Request::isPost()) {
    Response::error('Method not allowed.', 405);
}

Auth::requireRole(['cashier', 'admin']);
Csrf::guard();

(new OrderController())->createPos();
