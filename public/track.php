<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\View;

$code = trim((string) Request::get('code', ''));

echo View::render('customer/track', [
    'title'   => 'Lacak Pesanan',
    'code'    => $code,
    'scripts' => ['js/pages/track.js?v=1'],
], 'public');
