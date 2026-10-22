<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['cashier']);

$user = Auth::user();
$name = (string) ($user['name'] ?? 'Kasir');

echo View::render('cashier/index', [
    'title'      => 'Panel Kasir',
    'activeMenu' => 'cashier',
    'name'       => $name,
], 'cashier');
