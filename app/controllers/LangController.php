<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Exceptions\ValidationException;
use App\Core\I18n;
use App\Core\Request;
use App\Core\Response;

/**
 * Controller untuk mengganti bahasa antarmuka (id, en, zh).
 */
final class LangController extends BaseController
{
    public function switch(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $lang  = strtolower(trim((string) ($input['lang'] ?? '')));

            if (!in_array($lang, I18n::SUPPORTED, true)) {
                throw new ValidationException('Bahasa tidak didukung.', ['lang' => 'Pilih id, en, atau zh.']);
            }

            I18n::setLang($lang);

            Response::json([
                'lang'         => $lang,
                'translations' => I18n::all($lang),
            ], 'Bahasa berhasil diubah.');
        }, method: 'POST', csrf: true);
    }
}
