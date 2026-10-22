<?php
declare(strict_types=1);

/**
 * Halaman POS Kasir (FR-POS-01..03, prd.md §6.10, §4.3, §12.4).
 */
require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['cashier', 'admin']);

echo View::render('cashier/pos', [
    'title'      => 'POS Kasir',
    'activeMenu' => 'pos',
    'scripts'    => ['js/pages/payment.js?v=1', 'js/pages/pos.js?v=1'],
], 'cashier');
