<?php

declare(strict_types=1);

namespace Unit;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use JsonException;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\MissingApiCredentialsException;
use SubscribeMe\Subscriber\BrevoSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class BrevoMailerTestCase extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $brevoSubscriber = new BrevoSubscriber($client, $factory, $factory);

        $email = 'elly@example.com';
        $options = [
            'FNAME' => 'Elly',
            'LNAME' => 'Roger',
            'COUNTRY' => [
                'India',
                'China'
            ]
        ];

        $brevoSubscriber->setContactListId('3,5');
        $brevoSubscriber->setApiKey('928f601b-5476-4480-8eb0-c8d979f3b68f');
        $brevoSubscriber->subscribe($email, $options);

        $requests = $client->getRequests();

        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => [3,5],
            'attributes' => $options
        ];
        $body = json_encode($body);

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('928f601b-5476-4480-8eb0-c8d979f3b68f', $requests[0]->getHeaders()['api-key'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('api.brevo.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSendTransactionalEmail(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $brevoSubscriber = new BrevoSubscriber($client, $factory, $factory);

        $emails[0] = new EmailAddress('jimmy98@example.com', 'Jimmy');
        $variables = [
            'FNAME' => 'Joe',
            'LNAME' => 'Doe'
        ];

        $templateEmail = 2;

        $brevoSubscriber->setApiKey('75620ec7-54ea-451d-ad0d-ab4f43f9879c');
        $brevoSubscriber->sendTransactionalEmail($emails, $templateEmail, $variables);

        $requests = $client->getRequests();

        $body = [
            'to' => [
                [
                    'email' => 'jimmy98@example.com',
                    'name' => 'Jimmy'
                ]
            ],
            'params' => $variables,
            'templateId' => $templateEmail,
        ];
        $body = json_encode($body);

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('75620ec7-54ea-451d-ad0d-ab4f43f9879c', $requests[0]->getHeaders()['api-key'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('api.brevo.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testExceptionApiKey(): void
    {
        $this->expectException(MissingApiCredentialsException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $brevoSubscriber = new BrevoSubscriber($client, $factory, $factory);
        $emails[0] = new EmailAddress('jimmy98@example.com', 'Jimmy');
        $brevoSubscriber->sendTransactionalEmail($emails, 2, ['FNAME' => 'Joe', 'LNAME' => 'Doe']);
    }
}
