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

final class Factory implements SubscriberFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    public function createFor(string $platform): SubscriberInterface
    {
        switch (strtolower($platform)) {
            case 'mailjet':
                return new MailjetSubscriber($this->client, $this->requestFactory, $this->streamFactory);
            case 'mailchimp':
                return new MailchimpSubscriber($this->client, $this->requestFactory, $this->streamFactory);
            case 'sendinblue':
            case 'brevo':
                return new BrevoSubscriber($this->client, $this->requestFactory, $this->streamFactory);
            case 'sendinblue-doi':
            case 'brevo-doi':
                return new BrevoDoubleOptInSubscriber($this->client, $this->requestFactory, $this->streamFactory);
            case 'ymlp':
                return new YmlpSubscriber($this->client, $this->requestFactory, $this->streamFactory);
        }
        throw new \InvalidArgumentException('No subscriber class found for ' . $platform);
    }
}
