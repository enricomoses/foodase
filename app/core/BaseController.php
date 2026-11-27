<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\ValidationException;
use Throwable;

/**
 * BaseController — menyediakan guard terpusat (HTTP method, role, CSRF),
 * validasi, dan pemetaan AppException menjadi JSON Response yang konsisten.
 */
abstract class BaseController
{
    /**
     * Eksekusi aksi API dengan guard terpusat dan penanganan exception seragam.
     *
     * @param callable          $action Callback aksi controller.
     * @param string|array|null $method HTTP method yang diizinkan ('GET', 'POST', dll.).
     * @param string[]|null     $roles  Role yang diizinkan (null = publik, [] = login wajib tanpa filter role).
     * @param bool              $csrf   Wajib validasi CSRF token (otomatis true untuk POST jika tidak dimatikan).
     */
    protected function handleApi(
        callable $action,
        string|array|null $method = null,
        ?array $roles = null,
        ?bool $csrf = null
    ): void {
        if ($method !== null) {
            Request::requireMethod($method);
        }

        if ($roles !== null) {
            if ($roles === []) {
                Auth::requireLogin();
            } else {
                Auth::requireRole($roles);
            }
        }

        $shouldCheckCsrf = $csrf ?? ($method === 'POST' || (is_array($method) && in_array('POST', $method, true)));
        if ($shouldCheckCsrf) {
            Csrf::guard();
        }

        try {
            $action();
        } catch (AppException $e) {
            Response::error($e->getMessage(), $e->statusCode(), $e->errors());
        } catch (Throwable $e) {
            log_error($e);
            Response::error('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Validasi input menggunakan Validator dan lempar ValidationException jika gagal.
     *
     * @param array<string, mixed>  $data
     * @param array<string, string|array<int, string>> $rules
     * @param array<string, string> $messages
     * @return array<string, mixed>
     */
    protected function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            throw new ValidationException('Validasi gagal.', $validator->firstErrors());
        }

        return $validator->validated();
    }
}
