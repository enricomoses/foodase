<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['cashier', 'admin']);

$activeMenu = Auth::role() === 'admin' ? 'orders' : 'cashier';

echo View::render('cashier/orders', [
    'title'      => 'Pesanan Masuk',
    'activeMenu' => $activeMenu,
    'scripts'    => ['js/pages/payment.js?v=1', 'js/pages/cashier-orders.js?v=3'],
], 'cashier');
