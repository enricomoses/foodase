<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception untuk seluruh error domain / aplikasi yang memiliki HTTP status code.
 */
class AppException extends RuntimeException
{
    /**
     * @param array<string, string|string[]> $errors
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 400,
        private readonly array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
