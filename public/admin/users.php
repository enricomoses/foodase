<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

echo View::render('admin/users', [
    'title'      => 'Kelola Akun Kasir',
    'activeMenu' => 'users',
    'scripts'    => ['js/pages/admin-users.js'],
], 'admin');
