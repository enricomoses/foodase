<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

$motionFile = PUBLIC_PATH . '/assets/js/cafe-motion.js';
$menuFile   = PUBLIC_PATH . '/assets/js/pages/menu.js';

$motionVersion = file_exists($motionFile) ? (string) filemtime($motionFile) : '30';
$menuVersion   = file_exists($menuFile) ? (string) filemtime($menuFile) : '80';

$scripts = [
    'js/cafe-motion.js?v=' . $motionVersion,
    'js/pages/menu.js?v=' . $menuVersion,
];

echo View::render('customer/menu', [
    'title'     => 'Menu',
    'immersive' => true,
    'scripts'   => $scripts,
], 'public');