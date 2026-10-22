<?php
declare(strict_types=1);

// Jalankan dari terminal: C:\xampp\php\php.exe database\seed-users.php
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$users = [
    // [nama, username, password awal, role]
    ['Administrator', 'admin',  'admin12345', 'admin'],
    ['Kasir Satu',    'kasir1', 'kasir12345', 'cashier'],
];

$pdo  = Database::connection();
$stmt = $pdo->prepare(
    'INSERT INTO users (name, username, password_hash, role)
     VALUES (:name, :username, :hash, :role)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role)'
);

foreach ($users as [$name, $username, $password, $role]) {
    $stmt->execute([
        ':name'     => $name,
        ':username' => $username,
        ':hash'     => password_hash($password, PASSWORD_DEFAULT),
        ':role'     => $role,
    ]);
    echo "OK: {$username} ({$role})\n";
}