<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

$menuFile    = PUBLIC_PATH . '/assets/js/pages/menu.js';
$menuVersion = is_file($menuFile) ? (string) filemtime($menuFile) : '1';

echo View::render('customer/menu', [
    'title'     => 'Menu',
    'immersive' => true,
    'scripts'   => ['js/pages/menu.js?v=' . $menuVersion],
], 'public');
