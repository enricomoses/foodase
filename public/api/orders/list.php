<?php
declare(strict_types=1);

/**
 * GET api/orders/list.php
 * Daftar pesanan kasir/admin dengan filter dan paginasi (FR-COR-01, FR-COR-02).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;
use App\Core\Request;
use App\Core\Response;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new OrderController())->list();
