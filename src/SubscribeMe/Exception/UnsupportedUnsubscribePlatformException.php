<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

final class UnsupportedUnsubscribePlatformException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('The platform does not support unsubscribing email addresses.', 0);
    }
}
