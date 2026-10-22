<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

$user = Auth::user();
$name = (string) ($user['name'] ?? 'Administrator');

echo View::render('admin/index', [
    'title'      => 'Dashboard Admin',
    'activeMenu' => 'dashboard',
    'name'       => $name,
], 'admin');
