<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Config;
use App\Core\View;

$maxTable = (int) Config::get('order.max_table', 30);

echo View::render('customer/checkout', [
    'title'    => 'Checkout',
    'max_table' => $maxTable,
    'scripts'  => ['js/pages/checkout.js?v=1'],
], 'public');
