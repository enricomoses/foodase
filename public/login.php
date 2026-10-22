<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

// Jika pengguna sudah login, arahkan langsung ke halaman sesuai rolenya
if (Auth::check()) {
    $role = Auth::role();
    if ($role === 'admin') {
        Response::redirect('admin/');
    } else {
        Response::redirect('cashier/');
    }
}

echo View::render('auth/login', [
    'title'   => 'Masuk',
    'scripts' => ['js/pages/login.js'],
], 'auth');
