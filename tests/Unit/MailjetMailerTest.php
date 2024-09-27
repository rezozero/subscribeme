<?php

declare(strict_types=1);

namespace Unit;

use Http\Discovery\Psr17Factory;
use Http\Mock\Client;
use JsonException;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\ApiResponseException;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Subscriber\MailjetSubscriber;
use SubscribeMe\ValueObject\EmailAddress;

class MailjetMailerTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testSubscribe(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $client->setDefaultResponse(new Response(200, [], json_encode(['Total' => 1, 'Data' => [['ContactID' => 1]] ], JSON_THROW_ON_ERROR)));

        $mailjetSubscriber = new MailjetSubscriber($client, $factory, $factory);

        $options = [
            'Name' => 'Passenger 1',
        ];

        $mailjetSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailjetSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $returnCode = $mailjetSubscriber->subscribe("passenger@mailjet.com", $options);

        $requests = $client->getRequests();

        $body = [
            'Action' => 'addnoforce',
            'Email' => 'passenger@mailjet.com',
            'Name' => 'Passenger 1',
            'Properties' => []
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
        $this->assertEquals('api.mailjet.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSubscribeWithCodeError(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();

        $client->setDefaultResponse(new Response(400));

        $mailjetSubscriber = new MailjetSubscriber($client, $factory, $factory);

        $options = [
            'Name' => 'Passenger 1',
        ];

        $mailjetSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailjetSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $returnCode = $mailjetSubscriber->subscribe("passenger@mailjet.com", $options);

        $requests = $client->getRequests();

        $body = [
            'Action' => 'addnoforce',
            'Email' => 'passenger@mailjet.com',
            'Name' => 'Passenger 1',
            'Properties' => []
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
        $this->assertEquals('api.mailjet.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testSendTransactionalEmail(): void
    {
        $client = new Client();
        $factory = new Psr17Factory();
        $mailjetSubscriber = new MailjetSubscriber($client, $factory, $factory);

        $emails = [
            new EmailAddress('passenger1@mailjet.com', 'passenger 1')
        ];
        $variables = [
            'day' => 'Tuesday',
            'personalmessage' => 'Happy birthday!'
        ];
        $emailTemplateId = 1;

        $mailjetSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailjetSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $mailjetSubscriber->sendTransactionalEmail($emails, $emailTemplateId, $variables);

        $requests = $client->getRequests();

        $body = [
            'Messages' => [[
                'To' => [
                    [
                        'email' => 'passenger1@mailjet.com',
                        'name' => 'passenger 1'
                    ]
                ],
                'Variables' => $variables,
                'TemplateID' => $emailTemplateId,
                'TemplateLanguage' => true,
            ]]
        ];
        $body = json_encode($body);

        $this->assertCount(1, $requests);
        $content = $requests[0]->getBody()->getContents();
        $this->assertEquals('application/json', $requests[0]->getHeaders()['Content-Type'][0]);
        $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', '3f62c1f4-efb7-4bc7-b76d-0c2217d307b0', 'df30148e-6cda-43ae-8665-9904f5f4f12a')), $requests[0]->getHeaders()['Authorization'][0]);
        $this->assertEquals('rezozero/subscribeme', $requests[0]->getHeaders()['User-Agent'][0]);
        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertJsonStringEqualsJsonString($body ?: '{}', $content);
        $this->assertEquals('api.mailjet.com', $requests[0]->getUri()->getHost());
    }

    /**
     * @throws JsonException
     */
    public function testExceptionApiKey(): void
    {
        $this->expectException(ApiCredentialsException::class);
        $client = new Client();
        $factory = new Psr17Factory();
        $mailjetSubscriber = new MailjetSubscriber($client, $factory, $factory);
        $emails = [
            new EmailAddress('passenger1@mailjet.com', 'passenger 1')
        ];
        $variables = ['day' => 'Tuesday', 'personalmessage' => 'Happy birthday!'];
        $mailjetSubscriber->sendTransactionalEmail($emails, 1, $variables);
    }

    /**
     * @throws JsonException
     */
    public function testApiErrorException(): void
    {
        $this->expectException(CannotSendTransactionalEmailException::class);
        $this->expectExceptionMessage('API key authentication/authorization failure.');
        $client = new Client();
        $factory = new Psr17Factory();
        $client->setDefaultResponse(new Response(401, [], json_encode(['ErrorMessage' => 'API key authentication/authorization failure.'], JSON_THROW_ON_ERROR)));
        $mailjetSubscriber = new MailjetSubscriber($client, $factory, $factory);
        $mailjetSubscriber->setApiKey('3f62c1f4-efb7-4bc7-b76d-0c2217d307b0');
        $mailjetSubscriber->setApiSecret('df30148e-6cda-43ae-8665-9904f5f4f12a');
        $emails = [
            new EmailAddress('passenger1@mailjet.com', 'passenger 1')
        ];
        $variables = ['day' => 'Tuesday', 'personalmessage' => 'Happy birthday!'];
        $mailjetSubscriber->sendTransactionalEmail($emails, 1, $variables);
    }
}
