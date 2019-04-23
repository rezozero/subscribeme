<?php
/**
 * subscribeme - Factory.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe;

use GuzzleHttp\Client;
use SubscribeMe\Subscriber\MailchimpSubscriber;
use SubscribeMe\Subscriber\MailjetSubscriber;
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
        switch (strtolower($platform)) {
            case 'mailjet':
                return new MailjetSubscriber(new Client());
            case 'mailchimp':
                return new MailchimpSubscriber(new Client());
        }
        throw new \InvalidArgumentException('No subscriber class found for ' . $platform);
    }
}
