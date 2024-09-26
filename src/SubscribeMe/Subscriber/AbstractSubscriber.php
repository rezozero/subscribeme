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

    /***
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @return ClientInterface
     */
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

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string|null $apiKey
     *
     * @return SubscriberInterface
     */
    public function setApiKey(?string $apiKey): SubscriberInterface
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    /**
     * @param string|null $apiSecret
     *
     * @return SubscriberInterface
     */
    public function setApiSecret(?string $apiSecret): SubscriberInterface
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactListId(): ?string
    {
        return $this->contactListId;
    }

    /**
     * @param string|null $contactListId List ID (may be multiple comma-separated)
     *
     * @return SubscriberInterface
     */
    public function setContactListId(?string $contactListId): SubscriberInterface
    {
        $this->contactListId = $contactListId;

        return $this;
    }
}
