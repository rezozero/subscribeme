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
}
