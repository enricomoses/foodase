<?php
declare(strict_types=1);

use App\Core\Config;

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Autoloader: App\Core\Database -> app/core/Database.php (folder huruf kecil, file PascalCase)
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $parts = explode('\\', substr($class, strlen($prefix)));
    $file  = array_pop($parts);
    $dir   = $parts ? strtolower(implode('/', $parts)) . '/' : '';
    $path  = APP_PATH . '/' . $dir . $file . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require APP_PATH . '/core/helpers.php';

Config::load(APP_PATH . '/config/config.php');

date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Jakarta'));

error_reporting(E_ALL);
ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');