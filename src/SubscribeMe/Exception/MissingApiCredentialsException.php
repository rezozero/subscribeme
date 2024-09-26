<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class MissingApiCredentialsException extends \RuntimeException
{
    /**
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Missing API credentials', 0, $previous);
    }
}
