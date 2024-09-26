<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class ApiCredentialsException extends \RuntimeException
{
    /**
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'Check API credentials', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
