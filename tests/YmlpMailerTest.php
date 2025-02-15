<?php

declare(strict_types=1);

namespace SubscribeMe\Test;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use JsonException;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;
use SubscribeMe\Subscriber\YmlpSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class YmlpMailerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(200, [], json_encode(['Code' => '3'], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

        $ymlpSubscriber = new YmlpSubscriber($client, $factory, $factory);

        $ymlpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $ymlpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $ymlpSubscriber->setContactListId('123');
        $returnCode = $ymlpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = [
            'Key' => $ymlpSubscriber->getApiSecret(),
            'Username' => $ymlpSubscriber->getApiKey(),
            'OverruleUnsubscribedBounced' => 0,
            'Email' => 'jdoe@example.com',
            'GroupID' => 123,
            'Output' => 'JSON'
        ];
        $body = http_build_query($body, '', '&');

        $this->assertTrue($returnCode);
        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('x-www-form-urlencoded', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertEquals($body, $content);
        $this->assertEquals('www.ymlp.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSubscribeWithoutCode(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(
            200,
            [],
            json_encode(['Output' => 'Email address already in selected groups'], JSON_THROW_ON_ERROR)
        );
        $client->setDefaultResponse($response);

        $ymlpSubscriber = new YmlpSubscriber($client, $factory, $factory);

        $ymlpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $ymlpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $ymlpSubscriber->setContactListId('123');
        $returnCode = $ymlpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = "Key=df30148e-6cda-43ae-8665-9904f5f4f12a&Username=3f62c1f4-efb7-4bc7-b76d-0c2217d307b0&OverruleUnsubscribedBounced=0&Email=jdoe%40example.com&GroupID=123&Output=JSON";

        $this->assertTrue($returnCode);
        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('x-www-form-urlencoded', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertEquals($body, $content);
        $this->assertEquals('www.ymlp.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSendTransactionalEmail(): void
    {
        $this->expectException(UnsupportedTransactionalEmailPlatformException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $ymlpSubscriber = new YmlpSubscriber($client, $factory, $factory);
        $emails = [
            new EmailAddress('jdoe@example.com', 'John Doe')
        ];
        $ymlpSubscriber->sendTransactionalEmail($emails, '1', []);
    }
}
