<?php

declare(strict_types=1);

namespace Unit;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\PlatformNotSupportException;
use SubscribeMe\Subscriber\MailjetSubscriber;
use SubscribeMe\Subscriber\YmlpSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class YmlpMailerTestCase extends TestCase
{
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $ymlpSubscriber = new YmlpSubscriber($client, $factory, $factory);

        $ymlpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = "OverruleUnsubscribedBounced=0&Email=jdoe%40example.com&GroupID=0&Output=JSON";

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('x-www-form-urlencoded', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertEquals($body, $content);
        $this->assertEquals('www.ymlp.com', $requests[0]->getUri()->getHost());
    }

    public function testSendTransactionalEmail(): void
    {
        $this->expectException(PlatformNotSupportException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $ymlpSubscriber = new YmlpSubscriber($client, $factory, $factory);
        $emails[0] = new EmailAddress('passenger1@mailjet.com', 'passenger 1');
        $ymlpSubscriber->sendTransactionalEmail($emails, [], '1');
    }
}
