<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

/**
 * Exception akses ditolak (HTTP 403 Forbidden).
 */
final class ForbiddenException extends AppException
{
    public function __construct(string $message = 'Akses ditolak.')
    {
        parent::__construct($message, 403);
    }
}
