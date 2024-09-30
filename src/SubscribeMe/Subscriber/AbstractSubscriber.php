<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

abstract class AbstractSubscriber implements SubscriberInterface
{
    private ?string $apiKey = null;
    private ?string $apiSecret = null;
    private ?string $contactListId = null;

    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): SubscriberInterface
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    public function setApiSecret(?string $apiSecret): SubscriberInterface
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    public function getContactListId(): ?string
    {
        return $this->contactListId;
    }

    /**
     * @param string|null $contactListId List ID (maybe multiple comma-separated)
     */
    public function setContactListId(?string $contactListId): SubscriberInterface
    {
        $this->contactListId = $contactListId;

        return $this;
    }
}
