<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

echo View::render('customer/cart', [
    'title'   => 'Keranjang',
    'scripts' => ['js/pages/cart.js?v=1'],
], 'public');
