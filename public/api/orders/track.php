<?php
declare(strict_types=1);

/**
 * GET api/orders/track.php?code=...&name=...
 * Pelacakan status pesanan publik (FR-TRK-01, FR-TRK-04, BR-17).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;
use App\Core\Request;
use App\Core\Response;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new OrderController())->track();
