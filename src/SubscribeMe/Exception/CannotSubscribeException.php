<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

class CannotSubscribeException extends \RuntimeException
{
    /**
     * @param string         $reason
     * @param Throwable|null $previous
     */
    public function __construct(string $reason = '', Throwable $previous = null)
    {
        if ('' != $reason) {
            parent::__construct('Cannot subscribe email to platform: ' . $reason, 0, $previous);
        } else {
            parent::__construct('Cannot subscribe email to platform', 0, $previous);
        }
    }
}
