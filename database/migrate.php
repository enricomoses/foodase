<?php
declare(strict_types=1);

/**
 * CLI Migration & Seeder Runner (Native PHP + PDO).
 *
 * Penggunaan:
 *   php database/migrate.php                 Pasang skema bila belum terpasang
 *   php database/migrate.php --fresh         Drop & buat ulang DB dari 0 + pasang schema.sql
 *   php database/migrate.php --seed          Jalankan seeder secara idempotent
 *   php database/migrate.php --fresh --seed  Reset DB dari 0 lalu jalankan seluruh seeder
 *   php database/migrate.php --status        Tampilkan status tabel & migrasi
 *   php database/migrate.php --help          Tampilkan bantuan perintah
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/seeders/DatabaseSeeder.php';

use App\Core\Config;
use App\Core\Database;
use Database\Seeders\DatabaseSeeder;

// Ganti handler web dari bootstrap.php dengan output teks CLI yang bersih
set_exception_handler(static function (Throwable $e): void {
    log_error($e);
    fwrite(STDERR, "[ERROR] " . $e->getMessage() . PHP_EOL);
    exit(1);
});

const INITIAL_MIGRATION_NAME = '0001_initial_schema';
const SCHEMA_FILE_PATH       = __DIR__ . '/schema.sql';

exit(main($argv));

/**
 * Entry point CLI runner.
 *
 * @param array<int, string> $argv
 */
function main(array $argv): int
{
    $flags = parseFlags(array_slice($argv, 1));

    if ($flags['help']) {
        printHelp();
        return 0;
    }

    $dbName  = validatedDbName((string) Config::get('db.name', ''));
    $charset = (string) Config::get('db.charset', 'utf8mb4');
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $charset)) {
        throw new RuntimeException("Charset database tidak valid: '{$charset}'.");
    }
    $collate = $charset === 'utf8mb4' ? 'utf8mb4_unicode_ci' : "{$charset}_general_ci";

    if ($flags['status']) {
        showStatus($dbName);
        return 0;
    }

    $startedAt = microtime(true);
    $serverPdo = connectServer();

    if ($flags['fresh']) {
        echo "Mereset database `{$dbName}` dari 0..." . PHP_EOL;
        $serverPdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
        $serverPdo->exec(
            "CREATE DATABASE `{$dbName}` CHARACTER SET {$charset} COLLATE {$collate}"
        );
        applySchema($dbName);
    } else {
        $serverPdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collate}"
        );

        $pdo = Database::connection();
        if (!tableExists($pdo, $dbName, 'schema_migrations')) {
            echo "Menyiapkan skema awal pada database `{$dbName}`..." . PHP_EOL;
            applySchema($dbName);
        } else {
            echo "Skema database `{$dbName}` sudah terpasang (gunakan --fresh untuk reset dari 0)." . PHP_EOL;
        }
    }

    if ($flags['seed']) {
        echo "Menjalankan DatabaseSeeder..." . PHP_EOL;
        $summary = DatabaseSeeder::run();
        foreach ($summary as $entity => $count) {
            echo "  - {$entity}: {$count} baris diproses" . PHP_EOL;
        }
    }

    $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);
    echo "Selesai dalam {$elapsedMs} ms." . PHP_EOL;

    return 0;
}

/**
 * Parsing argumen flag CLI.
 *
 * @param array<int, string> $args
 * @return array{fresh: bool, seed: bool, status: bool, help: bool}
 */
function parseFlags(array $args): array
{
    $allowed = ['--fresh', '--seed', '--status', '--help', '-h'];
    foreach ($args as $arg) {
        if (!in_array($arg, $allowed, true)) {
            throw new InvalidArgumentException(
                "Argumen tidak dikenal: {$arg}. Gunakan --help untuk melihat daftar perintah."
            );
        }
    }

    return [
        'fresh'  => in_array('--fresh', $args, true),
        'seed'   => in_array('--seed', $args, true),
        'status' => in_array('--status', $args, true),
        'help'   => in_array('--help', $args, true) || in_array('-h', $args, true),
    ];
}

/**
 * Validasi nama database dari konfigurasi agar aman dipakai sebagai identifier SQL.
 */
function validatedDbName(string $dbName): string
{
    $dbName = trim($dbName);
    if ($dbName === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
        throw new RuntimeException(
            "Nama database DB_NAME ('{$dbName}') tidak valid. Hanya huruf, angka, dan underscore yang diizinkan."
        );
    }

    return $dbName;
}

/**
 * Buat koneksi PDO ke server MySQL/MariaDB tanpa memilih database tertentu.
 */
function connectServer(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;charset=%s',
        (string) Config::get('db.host', '127.0.0.1'),
        (int) Config::get('db.port', 3306),
        (string) Config::get('db.charset', 'utf8mb4')
    );

    $pdo = new PDO(
        $dsn,
        (string) Config::get('db.user', 'root'),
        (string) Config::get('db.pass', ''),
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $pdo->exec("SET time_zone = '+07:00'");

    return $pdo;
}

/**
 * Cek apakah tabel tertentu ada di database target.
 */
function tableExists(PDO $pdo, string $dbName, string $tableName): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = :schema AND table_name = :table'
    );
    $stmt->execute([
        ':schema' => $dbName,
        ':table'  => $tableName,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Jalankan file schema.sql dan catat versi awal ke schema_migrations.
 */
function applySchema(string $dbName): void
{
    if (!is_file(SCHEMA_FILE_PATH)) {
        throw new RuntimeException('File schema.sql tidak ditemukan di ' . SCHEMA_FILE_PATH);
    }

    $sql = file_get_contents(SCHEMA_FILE_PATH);
    if ($sql === false) {
        throw new RuntimeException('Gagal membaca file schema.sql.');
    }

    $statements = splitSqlStatements($sql);
    $pdo        = Database::connection();

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO schema_migrations (migration, batch)
         VALUES (:migration, :batch)
         ON DUPLICATE KEY UPDATE batch = VALUES(batch), executed_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([
        ':migration' => INITIAL_MIGRATION_NAME,
        ':batch'     => 1,
    ]);

    echo "  - Skema diterapkan ke `{$dbName}` (" . count($statements) . " statement)." . PHP_EOL;
}

/**
 * Tampilkan status database, tabel, jumlah baris, dan riwayat migrasi.
 */
function showStatus(string $dbName): void
{
    $serverPdo = connectServer();
    $dbCheck   = $serverPdo->prepare(
        'SELECT SCHEMA_NAME FROM information_schema.schemata WHERE SCHEMA_NAME = :db'
    );
    $dbCheck->execute([':db' => $dbName]);

    if ($dbCheck->fetchColumn() === false) {
        echo "Database `{$dbName}` belum dibuat." . PHP_EOL;
        return;
    }

    $pdo    = Database::connection();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    echo "Database aktif: `{$dbName}`" . PHP_EOL;
    if (empty($tables)) {
        echo "Belum ada tabel di dalam database." . PHP_EOL;
        return;
    }

    echo PHP_EOL . "Daftar Tabel:" . PHP_EOL;
    foreach ($tables as $table) {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo sprintf("  - %-22s %d baris", $table, $count) . PHP_EOL;
    }

    if (tableExists($pdo, $dbName, 'schema_migrations')) {
        echo PHP_EOL . "Riwayat Migrasi:" . PHP_EOL;
        $migrations = $pdo->query(
            'SELECT migration, batch, executed_at FROM schema_migrations ORDER BY id ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($migrations as $row) {
            echo sprintf(
                "  [Batch %d] %-28s (%s)",
                (int) $row['batch'],
                (string) $row['migration'],
                (string) $row['executed_at']
            ) . PHP_EOL;
        }
    }
}

/**
 * Pecah isi file SQL menjadi array statement yang aman tanpa merusak string literal/komentar.
 *
 * @return array<int, string>
 */
function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer     = '';
    $length     = strlen($sql);

    $inSingleQuote = false;
    $inDoubleQuote = false;
    $inBacktick    = false;
    $inLineComment = false;
    $inBlockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($char === "\n") {
                $inLineComment = false;
            }
            continue;
        }

        if ($inBlockComment) {
            if ($char === '*' && $next === '/') {
                $inBlockComment = false;
                $i++;
            }
            continue;
        }

        if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
            $afterDash = $sql[$i + 2] ?? ' ';
            if ($char === '-' && $next === '-' && ctype_space($afterDash)) {
                $inLineComment = true;
                $i++;
                continue;
            }
            if ($char === '#') {
                $inLineComment = true;
                continue;
            }
            if ($char === '/' && $next === '*') {
                $inBlockComment = true;
                $i++;
                continue;
            }
        }

        if ($char === "'" && !$inDoubleQuote && !$inBacktick) {
            $backslashes = 0;
            for ($j = $i - 1; $j >= 0 && $sql[$j] === '\\'; $j--) {
                $backslashes++;
            }
            if ($backslashes % 2 === 0) {
                $inSingleQuote = !$inSingleQuote;
            }
        } elseif ($char === '"' && !$inSingleQuote && !$inBacktick) {
            $backslashes = 0;
            for ($j = $i - 1; $j >= 0 && $sql[$j] === '\\'; $j--) {
                $backslashes++;
            }
            if ($backslashes % 2 === 0) {
                $inDoubleQuote = !$inDoubleQuote;
            }
        } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
            $inBacktick = !$inBacktick;
        }

        if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
            $trimmed = trim($buffer);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $trimmed = trim($buffer);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

/**
 * Cetak panduan penggunaan CLI.
 */
function printHelp(): void
{
    echo <<<TEXT
FoodEase Database Runner (Native PHP)

Penggunaan:
  php database/migrate.php                 Pasang skema bila belum terpasang
  php database/migrate.php --fresh         Drop & buat ulang database dari 0 lalu pasang schema.sql
  php database/migrate.php --seed          Jalankan semua seeder secara idempotent
  php database/migrate.php --fresh --seed  Reset database dari 0 dan jalankan semua seeder
  php database/migrate.php --status        Tampilkan daftar tabel, jumlah baris, dan status migrasi
  php database/migrate.php --help          Tampilkan pesan bantuan ini

TEXT;
}
