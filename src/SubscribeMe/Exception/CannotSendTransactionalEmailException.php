<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class CannotSendTransactionalEmailException extends \LogicException
{
    /**
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Cannot send transactional email to platform', 0, $previous);
    }
}
