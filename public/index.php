<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

$threeFile = PUBLIC_PATH . '/assets/vendor/three/three.min.js';
$motionFile = PUBLIC_PATH . '/assets/js/cafe-motion.js';
$menuFile = PUBLIC_PATH . '/assets/js/pages/menu.js';

$threeVersion  = file_exists($threeFile) ? (string) filemtime($threeFile) : '128';
$motionVersion = file_exists($motionFile) ? (string) filemtime($motionFile) : '30';
$menuVersion   = file_exists($menuFile) ? (string) filemtime($menuFile) : '80';

$scripts = [];
if (file_exists($threeFile)) {
    $scripts[] = 'vendor/three/three.min.js?v=' . $threeVersion;
}
$scripts[] = 'js/cafe-motion.js?v=' . $motionVersion;
$scripts[] = 'js/pages/menu.js?v=' . $menuVersion;

echo View::render('customer/menu', [
    'title'     => 'Menu',
    'immersive' => true,
    'scripts'   => $scripts,
], 'public');