<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

class BrevoDoubleOptInSubscriber extends SendInBlueDoubleOptInSubscriber
{
    public function getPlatform(): string
    {
        return 'brevo';
    }
}
