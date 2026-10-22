<?php
declare(strict_types=1);

/**
 * GET api/orders/mine.php
 * Mengambil daftar riwayat pesanan dari session pelanggan (FR-TRK-03).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;
use App\Core\Request;
use App\Core\Response;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new OrderController())->mine();
