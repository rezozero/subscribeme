<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class CannotSendTransactionalEmailException extends \RuntimeException
{
    /**
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'Cannot send transactional email to platform', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
