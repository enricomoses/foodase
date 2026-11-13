<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

// Jika pengguna sudah login sebagai staf / admin, arahkan ke dashboard masing-masing
if (Auth::check()) {
    $role = Auth::role();
    if ($role === 'admin') {
        Response::redirect('admin/');
    } elseif ($role === 'cashier') {
        Response::redirect('cashier/');
    } else {
        Response::redirect('');
    }
}

echo View::render('admin/login', [
    'title'   => 'Login Portal Staf & Administrator',
    'scripts' => ['js/pages/admin-login.js?v=12'],
], 'auth');

