<?php
/**
 * subscribeme - AbstractSubscriber.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use GuzzleHttp\Client;

abstract class AbstractSubscriber implements SubscriberInterface
{
    /** @var Client */
    private $client;
    /** @var string|null */
    private $apiKey;
    /** @var string|null */
    private $apiSecret;
    /** @var string|null */
    private $contactListId;

    /**
     * AbstractSubscriber constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return string
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
     * @return string
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
     * @return string
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
