<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                Config::get('db.host'),
                Config::get('db.port'),
                Config::get('db.name'),
                Config::get('db.charset')
            );

            self::$pdo = new PDO($dsn, (string) Config::get('db.user'), (string) Config::get('db.pass'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::$pdo->exec("SET time_zone = '+07:00'");
        }
        return self::$pdo;
    }

    /**
     * Jalankan callback di dalam transaksi DB dengan commit/rollback otomatis.
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $isNested = $pdo->inTransaction();

        if (!$isNested) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);

            if (!$isNested) {
                $pdo->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if (!$isNested && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}