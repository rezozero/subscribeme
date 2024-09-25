<?php

declare(strict_types=1);

namespace Unit;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Subscriber\MailchimpSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class MailchimpMailerTestCase extends TestCase
{
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $mailchimpSubscriber->setContactListId('1');
        $mailchimpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = [
            'status' => 'subscribed',
            'email_address' => 'jdoe@example.com',
        ];
        $body = json_encode($body);

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertStringContainsString('Basic', $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('us16.api.mailchimp.com', $requests[0]->getUri()->getHost());
    }

    public function testSendTransactionalEmail(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $emails[0] = new EmailAddress('jdoe@example.com', 'John Doe');
        $variables = [
            'test' => 'test',
        ];
        $templateEmail = 'template_name';

        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->sendTransactionalEmail($emails, $variables, $templateEmail);

        $requests = $client->getRequests();

        $body = [
            'template_name' => 'template_name',
            'template_content' => [
                'name' => 'template_name',
                'content' => ['test'=> 'test']
            ],
            'message' => [
                'to' => [[
                    'email' => 'jdoe@example.com',
                    'name' => 'John Doe',
                    'type' => 'to',
                ]]
            ],
            'key' => '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0'
        ];
        $body = json_encode($body);

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('mandrillapp.com', $requests[0]->getUri()->getHost());
    }

    public function testExceptionApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);
        $emails[0] = new EmailAddress('jdoe@example.com', 'John Doe');
        $mailchimpSubscriber->sendTransactionalEmail($emails, ['test' => 'test'], 'template_name');
    }
}
