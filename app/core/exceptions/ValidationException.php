<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

/**
 * Exception validasi input atau aturan bisnis (HTTP 422 Unprocessable Entity).
 */
final class ValidationException extends AppException
{
    /**
     * @param array<string, string|string[]> $errors
     */
    public function __construct(string $message = 'Data tidak valid.', array $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}
