<?php

declare(strict_types=1);

namespace SubscribeMe;

use GuzzleHttp\Client;
use SubscribeMe\Subscriber\BrevoDoubleOptInSubscriber;
use SubscribeMe\Subscriber\BrevoSubscriber;
use SubscribeMe\Subscriber\MailchimpSubscriber;
use SubscribeMe\Subscriber\MailjetSubscriber;
use SubscribeMe\Subscriber\SubscriberInterface;
use SubscribeMe\Subscriber\YmlpSubscriber;

class Factory
{
    /**
     * @param string $platform
     *
     * @return SubscriberInterface
     */
    public static function createFor(string $platform): SubscriberInterface
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => 'rezozero/subscribeme'
            ]
        ]);
        switch (strtolower($platform)) {
            case 'mailjet':
                return new MailjetSubscriber($client);
            case 'mailchimp':
                return new MailchimpSubscriber($client);
            case 'sendinblue':
            case 'brevo':
                return new BrevoSubscriber($client);
            case 'sendinblue-doi':
            case 'brevo-doi':
                return new BrevoDoubleOptInSubscriber($client);
            case 'ymlp':
                return new YmlpSubscriber($client);
        }
        throw new \InvalidArgumentException('No subscriber class found for ' . $platform);
    }
}
