<?php

declare(strict_types=1);

namespace SubscribeMe;

use SubscribeMe\Subscriber\SubscriberInterface;

interface SubscriberFactoryInterface
{
    /**
     * @param string $platform
     * @return SubscriberInterface
     */
    public function createFor(string $platform): SubscriberInterface;
}
