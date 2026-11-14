<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

echo View::render('customer/menu', [
    'title'     => 'Glacé — Artisan Café & Coffee',
    'immersive' => true,
    'scripts'   => ['js/pages/menu.js?v=50'],
], 'public');