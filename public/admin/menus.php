<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;
use App\Models\Category;

Auth::requireRole(['admin']);

// Ambil daftar kategori untuk dropdown filter & form
$categories = Category::all();

echo View::render('admin/menus', [
    'title'      => 'Kelola Menu',
    'activeMenu' => 'menus',
    'categories' => $categories,
    'scripts'    => ['js/pages/admin-menus.js'],
], 'admin');
