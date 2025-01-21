<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;

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

        if (!is_string($this->getContactListId())) {
            throw new CannotSubscribeException('Contact list id is required for subscribe');
        }

        $mode = $options['mode'] ?? 'ignore';
        unset($options['mode']);

        $contacts[$email] = $options;

        $body = [
            'mode' => $mode,
            'contacts' => json_encode($contacts, JSON_THROW_ON_ERROR),
        ];
        $queryParams = http_build_query($body);

        $uri = 'https://api.oximailing.com/lists/' . $this->getContactListId() . '/contacts?' . $queryParams;
        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', $uri)
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic '.base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            $res = $this->getClient()->sendRequest($request);


            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['added'] == 1 || $body['ignored'] == 1 || $body['updated'] == 1) {
                    return true;
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

    /**
     * @inheritdoc
     */
    public function unsubscribe(string $email): bool
    {
        if (!is_string($this->getApiKey())) {
            throw new ApiCredentialsException();
        }

        if (!is_string($this->getApiSecret())) {
            throw new ApiCredentialsException();
        }

        if (!is_string($this->getContactListId())) {
            throw new CannotSubscribeException('Contact list id is required for subscribe');
        }

        $queryParams = http_build_query(['emails' => $email]);

        $uri = 'https://api.oximailing.com/lists/' . $this->getContactListId() . '/contacts?' . $queryParams;
        try {
            $request = $this->getRequestFactory()
                ->createRequest('DELETE', $uri)
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic '.base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            $res = $this->getClient()->sendRequest($request);


            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['deleted'] == 1 || $body['not_found'] == 1) {
                    return true;
                }
            }
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }
        return false;
    }
}
