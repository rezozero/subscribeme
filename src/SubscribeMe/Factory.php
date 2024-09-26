<?php

declare(strict_types=1);

namespace SubscribeMe;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @return SubscriberInterface
     */
    public static function createFor(string $platform, ClientInterface $client, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory): SubscriberInterface
    {
        switch (strtolower($platform)) {
            case 'mailjet':
                return new MailjetSubscriber($client, $requestFactory, $streamFactory);
            case 'mailchimp':
                return new MailchimpSubscriber($client, $requestFactory, $streamFactory);
            case 'sendinblue':
            case 'brevo':
                return new BrevoSubscriber($client, $requestFactory, $streamFactory);
            case 'sendinblue-doi':
            case 'brevo-doi':
                return new BrevoDoubleOptInSubscriber($client, $requestFactory, $streamFactory);
            case 'ymlp':
                return new YmlpSubscriber($client, $requestFactory, $streamFactory);
        }
        throw new \InvalidArgumentException('No subscriber class found for ' . $platform);
    }
}
