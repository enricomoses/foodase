<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

if (Auth::check()) {
    $role = Auth::role();
    Response::redirect($role === 'admin' ? 'admin/' : 'cashier/');
}

echo View::render('auth/register', [
    'title'   => 'Daftar Akun Baru',
    'scripts' => ['js/pages/login.js'],
], 'auth');
