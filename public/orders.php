<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\View;

echo View::render('customer/history', [
    'title'   => 'Riwayat Pesanan',
    'scripts' => ['js/pages/history.js?v=1'],
], 'public');
