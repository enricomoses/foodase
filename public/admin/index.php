<?php
declare(strict_types=1);

/**
 * Dashboard Admin (FR-RPT-06, prd.md §6.14).
 * Ringkasan aktivitas dan performa penjualan kafe hari ini.
 */
require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

Auth::requireRole(['admin']);

$user = Auth::user();
$name = (string) ($user['name'] ?? 'Administrator');

echo View::render('admin/dashboard', [
    'title'      => 'Dashboard Admin',
    'activeMenu' => 'dashboard',
    'name'       => $name,
    'scripts'    => ['js/pages/admin-dashboard.js?v=2'],
], 'admin');
