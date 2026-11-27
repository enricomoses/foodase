<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

/**
 * Exception konflik state atau aturan bisnis (HTTP 409 Conflict).
 */
final class ConflictException extends AppException
{
    public function __construct(string $message = 'Terjadi konflik status data.')
    {
        parent::__construct($message, 409);
    }
}
