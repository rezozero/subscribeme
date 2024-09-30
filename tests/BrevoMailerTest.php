<?php

declare(strict_types=1);

namespace SubscribeMe\Test;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use JsonException;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Subscriber\BrevoSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class BrevoMailerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(200, [], json_encode(['id' => 1], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

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
        $returnCode = $brevoSubscriber->subscribe($email, $options);

        $requests = $client->getRequests();

        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => [3,5],
            'attributes' => $options
        ];
        $body = json_encode($body);

        $this->assertEquals(1, $returnCode);
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
    public function testSubscribeWithContactExist(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(400, [], json_encode(['message' => 'Contact already exist'], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

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
        $returnCode = $brevoSubscriber->subscribe($email, $options);

        $requests = $client->getRequests();

        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => [3,5],
            'attributes' => $options
        ];
        $body = json_encode($body);

        $this->assertTrue($returnCode);
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
    public function testSubscribeWithoutId(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(400);
        $client->setDefaultResponse($response);

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
        $returnCode = $brevoSubscriber->subscribe($email, $options);

        $requests = $client->getRequests();

        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => [3,5],
            'attributes' => $options
        ];
        $body = json_encode($body);

        $this->assertFalse($returnCode);
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

        $emails = [
            new EmailAddress('jimmy98@example.com', 'Jimmy')
        ];
        $variables = [
            'FNAME' => 'Joe',
            'LNAME' => 'Doe'
        ];

        $emailTemplateId = 2;

        $brevoSubscriber->setApiKey('75620ec7-54ea-451d-ad0d-ab4f43f9879c');
        $brevoSubscriber->sendTransactionalEmail($emails, $emailTemplateId, $variables);

        $requests = $client->getRequests();

        $body = [
            'to' => [
                [
                    'email' => 'jimmy98@example.com',
                    'name' => 'Jimmy'
                ]
            ],
            'params' => $variables,
            'templateId' => $emailTemplateId,
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
        $this->expectException(ApiCredentialsException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $brevoSubscriber = new BrevoSubscriber($client, $factory, $factory);
        $emails = [
            new EmailAddress('jimmy98@example.com', 'Jimmy')
        ];
        $brevoSubscriber->sendTransactionalEmail($emails, 2, ['FNAME' => 'Joe', 'LNAME' => 'Doe']);
    }

    /**
     * @throws JsonException
     */
    public function testApiErrorException(): void
    {
        $this->expectException(CannotSendTransactionalEmailException::class);
        $this->expectExceptionMessage('Key not found');
        $client = new Client();
        $factory = new Psr17Factory();
        $client->setDefaultResponse(new Response(401, [], json_encode(['message' => 'Key not found'], JSON_THROW_ON_ERROR)));
        $brevoSubscriber = new BrevoSubscriber($client, $factory, $factory);
        $brevoSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $emails = [
            new EmailAddress('jimmy98@example.com', 'Jimmy')
        ];
        $brevoSubscriber->sendTransactionalEmail($emails, 2, ['FNAME' => 'Joe', 'LNAME' => 'Doe']);
    }
}
