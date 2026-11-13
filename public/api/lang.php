<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Core\I18n;
use App\Core\Request;
use App\Core\Response;

$input = Request::input();
$lang = strtolower(trim((string) ($input['lang'] ?? $_GET['lang'] ?? '')));

if (in_array($lang, ['id', 'en', 'zh'], true)) {
    I18n::setLang($lang);
    Response::json([
        'lang' => $lang,
        'translations' => I18n::all($lang),
    ], 'Bahasa berhasil diubah.');
}

Response::error('Bahasa tidak didukung.', 400);

