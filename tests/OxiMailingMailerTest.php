<?php

declare(strict_types=1);

namespace SubscribeMe\Test;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use JsonException;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;
use SubscribeMe\Subscriber\OxiMailingSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class OxiMailingMailerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $client->setDefaultResponse(new Response(200, [], json_encode(['invalid' => 0, 'added' => 1, 'ignored' => 0, 'updated' => 0], JSON_THROW_ON_ERROR)));

        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);

        $oxiMailingSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $oxiMailingSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $oxiMailingSubscriber->setContactListId('123');
        $returnCode = $oxiMailingSubscriber->subscribe("tester@oximailing.com", ['mode' => 'ignored', 'firstName' => 'John', 'lastName' => 'Doe']);

        $requests = $client->getRequests();

        $this->assertTrue($returnCode);
        $this->assertCount(1, $requests);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertStringContainsString('api.oximailing.com', $requests[0]->getUri()->getHost());
        $this->assertStringContainsString('/lists/123/contacts', $requests[0]->getUri()->getPath());
    }

    /**
     * @throws JsonException
     */
    public function testSubscribeWithCodeError(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $client->setDefaultResponse(new Response(400));

        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);

        $oxiMailingSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $oxiMailingSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $oxiMailingSubscriber->setContactListId('123');
        $returnCode = $oxiMailingSubscriber->subscribe("tester@oximailing.com", ['mode' => 'ignored', 'firstName' => 'John', 'lastName' => 'Doe']);

        $requests = $client->getRequests();

        $this->assertFalse($returnCode);
        $this->assertCount(1, $requests);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertStringContainsString('api.oximailing.com', $requests[0]->getUri()->getHost());
        $this->assertStringContainsString('/lists/123/contacts', $requests[0]->getUri()->getPath());
    }

    /**
     * @throws JsonException
     */
    public function testExceptionListId(): void
    {
        $this->expectException(CannotSubscribeException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);
        $oxiMailingSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $oxiMailingSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $email = 'passenger1@mailjet.com';
        $oxiMailingSubscriber->unsubscribe($email);
    }

    /**
     * @throws JsonException
     */
    public function testSendTransactionalEmail(): void
    {
        $this->expectException(UnsupportedTransactionalEmailPlatformException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);
        $emails = [
            new EmailAddress('jdoe@example.com')
        ];
        $oxiMailingSubscriber->sendTransactionalEmail($emails, '1');
    }

    /**
     * @throws JsonException
     */
    public function testExceptionApiKey(): void
    {
        $this->expectException(ApiCredentialsException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);
        $emails = 'passenger1@mailjet.com';
        $oxiMailingSubscriber->subscribe($emails, ['mode' => 'update']);
    }

    /** @throws JsonException */
    public function testUnsubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $client->setDefaultResponse(new Response(200, [], json_encode(['deleted' => 1, 'not_found' => 0], JSON_THROW_ON_ERROR)));

        $oxiMailingSubscriber = new OxiMailingSubscriber($client, $factory, $factory);

        $oxiMailingSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $oxiMailingSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $oxiMailingSubscriber->setContactListId('123');
        $returnCode = $oxiMailingSubscriber->unsubscribe("tester@oximailing.com");

        $requests = $client->getRequests();

        $this->assertTrue($returnCode);
        $this->assertCount(1, $requests);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('DELETE', $requests[0]->getMethod());
        $this->assertStringContainsString('api.oximailing.com', $requests[0]->getUri()->getHost());
        $this->assertStringContainsString('/lists/123/contacts', $requests[0]->getUri()->getPath());
    }
}
