<?php

declare(strict_types=1);

namespace SubscribeMe;

use SubscribeMe\Subscriber\SubscriberInterface;

interface SubscriberFactoryInterface
{
    public function createFor(string $platform): SubscriberInterface;
}
