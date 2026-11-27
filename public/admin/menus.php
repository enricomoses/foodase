<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;
use App\Services\CategoryService;

Auth::requireRole(['admin']);

echo View::render('admin/menus', [
    'title'      => 'Kelola Menu',
    'activeMenu' => 'menus',
    'categories' => CategoryService::list(false),
    'scripts'    => ['js/pages/admin-menus.js'],
], 'app');
