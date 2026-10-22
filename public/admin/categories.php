<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

echo View::render('admin/categories', [
    'title'      => 'Kelola Kategori',
    'activeMenu' => 'categories',
    'scripts'    => ['js/pages/admin-categories.js'],
], 'admin');
