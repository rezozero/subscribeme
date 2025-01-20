<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\ApiResponseException;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;
use SubscribeMe\GDPR\UserConsent;
use SubscribeMe\ValueObject\EmailAddress;

class OxiMailingSubscriber extends AbstractSubscriber
{
    use ResponseValidationTrait;
    public function getPlatform(): string
    {
        return 'oximailing';
    }

    /**
     * @see https://api.oximailing.com/doc/#/contacts/post_lists__ListId__contacts
     * @inheritdoc
     */
    public function subscribe(string $email, array $options, array $userConsents = []): bool|int
    {
        if (!is_string($this->getApiKey())) {
            throw new ApiCredentialsException();
        }

        if (!is_string($this->getApiSecret())) {
            throw new ApiCredentialsException();
        }

        $body = [
            'mode' => $options['mode'],
            'contacts' => [$email],
        ];

        $uri = 'https://https://api.oximailing.com/lists/' . $this->getContactListId() . '/contacts';
        try {
            $bodyStreamed = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

            $request = $this->getRequestFactory()
                ->createRequest('POST', $uri)
                ->withBody($bodyStreamed)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic '.base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            $res = $this->getClient()->sendRequest($request);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['Total'] >= 1) {
                    return $body['Data'][0]['ContactID'];
                }
            }
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function sendTransactionalEmail(array $emails, string|int $emailTemplateId, array $variables = []): string
    {
        throw new UnsupportedTransactionalEmailPlatformException();
    }
}
