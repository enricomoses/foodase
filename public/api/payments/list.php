<?php
declare(strict_types=1);

/**
 * GET api/payments/list.php — Riwayat transaksi pembayaran (FR-HIS-01..02).
 * Kasir hanya melihat miliknya; admin melihat semua. Dipaksa di server.
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Controllers\PaymentController;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new PaymentController())->history();
