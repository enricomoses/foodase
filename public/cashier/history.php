<?php
declare(strict_types=1);

/**
 * Halaman Riwayat Transaksi Kasir/Admin (FR-HIS-01..03, prd.md §6.13, §12.4).
 */
require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['cashier', 'admin']);

$role = Auth::role();

echo View::render('cashier/history', [
    'title'      => 'Riwayat Transaksi',
    'activeMenu' => 'history',
    'role'       => $role,
    'scripts'    => ['js/pages/cashier-history.js?v=1'],
], 'cashier');
