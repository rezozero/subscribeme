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
use SubscribeMe\Subscriber\MailchimpSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class MailchimpMailerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(200, [], json_encode(['id' => 1 ,'title' => 'test'], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $mailchimpSubscriber->setContactListId('1');
        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $returnCode = $mailchimpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = [
            'status' => 'subscribed',
            'email_address' => 'jdoe@example.com',
        ];
        $body = json_encode($body);


        $this->assertEquals(1, $returnCode);
        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('us16.api.mailchimp.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSubscribeWithMemberExist(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(200, [], json_encode(['title' => 'Member Exists'], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $mailchimpSubscriber->setContactListId('1');
        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $returnCode = $mailchimpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = [
            'status' => 'subscribed',
            'email_address' => 'jdoe@example.com',
        ];
        $body = json_encode($body);


        $this->assertTrue($returnCode);
        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('us16.api.mailchimp.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSubscribeWithoutId(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $response = new Response(200, [], json_encode(['id' => null ,'title' => ''], JSON_THROW_ON_ERROR));
        $client->setDefaultResponse($response);

        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $mailchimpSubscriber->setContactListId('1');
        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $returnCode = $mailchimpSubscriber->subscribe("jdoe@example.com", []);

        $requests = $client->getRequests();

        $body = [
            'status' => 'subscribed',
            'email_address' => 'jdoe@example.com',
        ];
        $body = json_encode($body);

        $this->assertFalse($returnCode);
        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('us16.api.mailchimp.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSendTransactionalEmail(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);

        $emails = [
            new EmailAddress('jdoe@example.com', 'John Doe')
        ];
        $variables = [
            'test_string' => 'string content',
            'test_int' => 42,
            'test_bool' => true,
            'test_array' => [
                'nested_string' => 'nested content',
                'nested_int' => 100,
                'nested_bool' => false,
            ]
        ];
        $emailTemplateId = 'template_name';

        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->sendTransactionalEmail($emails, $emailTemplateId, $variables);

        $requests = $client->getRequests();

        $body = [
            'template_name' => 'template_name',
            'template_content' => [],
            'message' => [
                'to' => [[
                    'email' => 'jdoe@example.com',
                    'name' => 'John Doe',
                    'type' => 'to',
                ]],
                'global_merge_vars' => [
                    [
                        "content" => "string content",
                        "name" => "test_string"
                    ],
                    [
                        "content" => 42,
                        "name" => "test_int"
                    ],
                    [
                        "content" => true,
                        "name" => "test_bool"
                    ],
                    [
                        "content" => [
                            "nested_bool" => false,
                            "nested_int" => 100,
                            "nested_string" => "nested content"
                        ],
                        "name" => "test_array"
                    ],
                ]
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

    /**
     * @throws JsonException
     */
    public function testExceptionApiKey(): void
    {
        $this->expectException(ApiCredentialsException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);
        $emails = [
            new EmailAddress('jdoe@example.com', 'John Doe')
        ];
        $mailchimpSubscriber->sendTransactionalEmail($emails, 'template_name');
    }

    /**
     * @throws JsonException
     */
    public function testApiErrorException(): void
    {
        $this->expectException(CannotSendTransactionalEmailException::class);
        $this->expectExceptionMessage('Invalid API key');
        $client = new Client();
        $factory = new Psr17Factory();
        $client->setDefaultResponse(new Response(401, [], json_encode(['message' => 'Invalid API key'], JSON_THROW_ON_ERROR)));
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);
        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $emails = [
            new EmailAddress('jdoe@example.com', 'John Doe')
        ];
        $mailchimpSubscriber->sendTransactionalEmail($emails, 'template_name', ['name' => 'test', 'content' => 'content test']);
    }

    /**
     * @throws JsonException
     */
    public function testVariablesException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Variables signature was invalid');
        $client = new Client();
        $factory = new Psr17Factory();
        $mailchimpSubscriber = new MailchimpSubscriber($client, $factory, $factory);
        $mailchimpSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailchimpSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $emails = [
            new EmailAddress('jdoe@example.com', 'John Doe')
        ];
        $variables = [
            'valid_key' => 'valid content',
            123 => 'invalid key',
            'test_array' => [
                'nested_string' => 'nested content',
                'nested_invalid' => [1, 2, 3],
            ]
        ];
        // @phpstan-ignore-next-line
        $mailchimpSubscriber->sendTransactionalEmail($emails, 'template_name', $variables);
    }
}
