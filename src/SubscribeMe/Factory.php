<?php

declare(strict_types=1);

namespace SubscribeMe;

use GuzzleHttp\Client;
use SubscribeMe\Subscriber\MailchimpSubscriber;
use SubscribeMe\Subscriber\MailjetSubscriber;
use SubscribeMe\Subscriber\SendInBlueDoubleOptInSubscriber;
use SubscribeMe\Subscriber\SendInBlueSubscriber;
use SubscribeMe\Subscriber\YmlpSubscriber;
use SubscribeMe\Subscriber\SubscriberInterface;

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
                return new SendInBlueSubscriber($client);
            case 'sendinblue-doi':
                return new SendInBlueDoubleOptInSubscriber($client);
            case 'ymlp':
                return new YmlpSubscriber($client);
        }
        throw new \InvalidArgumentException('No subscriber class found for ' . $platform);
    }
}
