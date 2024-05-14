<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

class BrevoSubscriber extends SendInBlueSubscriber
{
    public function getPlatform(): string
    {
        return 'brevo';
    }
}
