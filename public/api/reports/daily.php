<?php
declare(strict_types=1);

/**
 * GET api/reports/daily.php — Pendapatan & transaksi harian (FR-RPT-05, admin only).
 */
require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\ReportController;
use App\Core\Request;
use App\Core\Response;

if (!Request::isGet()) {
    Response::error('Method not allowed.', 405);
}

(new ReportController())->daily();
