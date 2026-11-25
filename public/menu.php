<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

echo View::render('customer/menu', [
    'title'     => 'Menu',
    'immersive' => true,
    'scripts'   => ['js/pages/menu.js?v=60'],
], 'public');
