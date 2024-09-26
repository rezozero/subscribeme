<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class UnsupportedTransactionalEmailPlatformException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('The platform does not support transactional email', 0);
    }
}
