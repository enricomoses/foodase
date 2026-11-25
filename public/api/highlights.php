<?php
declare(strict_types=1);

/**
 * GET /api/highlights.php
 * Returns up to 6 top-selling available menu items with category.
 * Safe fallback: returns empty data array if DB unavailable or table missing.
 */

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\Database;

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=120');
header('X-Content-Type-Options: nosniff');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare(
        'SELECT
             m.id,
             m.name,
             CAST(m.price AS UNSIGNED) AS price,
             m.image_url,
             COALESCE(c.name, \'\') AS category
         FROM   menus       m
         LEFT   JOIN categories c ON c.id = m.category_id
         WHERE  m.is_available = 1
         ORDER  BY COALESCE(m.sold_count, 0) DESC, m.created_at DESC
         LIMIT  6'
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Resolve image_url to absolute URL if it's a relative path
    $baseUrl = rtrim((string) \App\Core\Config::get('app.base_url', '/foodease'), '/');
    foreach ($rows as &$row) {
        if (!empty($row['image_url']) && !str_starts_with($row['image_url'], 'http')) {
            $row['image_url'] = $baseUrl . '/' . ltrim($row['image_url'], '/');
        }
    }
    unset($row);

    echo json_encode(
        ['ok' => true, 'data' => $rows],
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
} catch (\Throwable $e) {
    // Safe fallback: DB not ready, table missing, or seeding not done yet
    log_error($e);
    echo json_encode(
        ['ok' => true, 'data' => [], 'note' => 'fallback'],
        JSON_UNESCAPED_UNICODE
    );
}
