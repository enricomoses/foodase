<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

echo View::render('cashier/orders', [
    'title'      => 'Kelola Pesanan',
    'activeMenu' => 'orders',
    'scripts'    => ['js/pages/payment.js?v=1', 'js/pages/cashier-orders.js?v=4'],
], 'admin');
