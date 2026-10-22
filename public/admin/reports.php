<?php
declare(strict_types=1);

/**
 * Halaman Laporan Penjualan Admin (FR-RPT-01..07, prd.md §6.14, §12.3).
 */
require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

echo View::render('admin/reports', [
    'title'      => 'Laporan Penjualan',
    'activeMenu' => 'reports',
    'scripts'    => ['js/pages/admin-reports.js?v=1'],
], 'admin');
