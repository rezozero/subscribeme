<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\ApiResponseException;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\GDPR\UserConsent;
use SubscribeMe\ValueObject\EmailAddress;

/**
 * @deprecated Use BrevoSubscriber instead
 */
class SendInBlueSubscriber extends AbstractSubscriber
{
    use ResponseValidationTrait;
    public function getPlatform(): string
    {
        return 'sendinblue';
    }

    /**
     * @return array<int>
     */
    protected function getListsId(): array
    {
        if (null === $this->getContactListId()) {
            throw new CannotSubscribeException('You must add a contact list ID before subscribing user.');
        }
        /*
         * SendInBlue supports multiple lists subscriptions
         * just use comma-separated ids.
         */
        $listIds = array_map(function (string $listId) {
            return (int) (trim($listId));
        }, array_filter(explode(',', trim($this->getContactListId()))));

        if (count($listIds) < 1) {
            throw new CannotSubscribeException('You must add at least one contact list ID before subscribing user.');
        }

        return $listIds;
    }

    /**
     * @param array $options
     * @param array $userConsents
     * @return array<string, mixed>
     */
    protected function getAttributes(array $options, array $userConsents = []): array
    {
        if (count($options) > 0) {
            $attributes = $options;
        }

        if (count($userConsents) > 0 && null !== $consent = $userConsents[0]) {
            if (!isset($attributes)) {
                $attributes = [];
            }
            if (!($consent instanceof UserConsent)) {
                throw new \InvalidArgumentException('User consent is not valid UserConsent object');
            }
            if (null !== $consent->getConsentFieldName()) {
                $attributes[$consent->getConsentFieldName()] = $consent->isConsentGiven();
            }
            if (null !== $consent->getDateFieldName() && null !== $consent->getConsentDate()) {
                $attributes[$consent->getDateFieldName()] = $consent->getConsentDate()->format('Y-m-d H:i:s');
            }
            if (null !== $consent->getIpAddressFieldName()) {
                $attributes[$consent->getIpAddressFieldName()] = $consent->getIpAddress();
            }
            if (null !== $consent->getReferrerFieldName()) {
                $attributes[$consent->getReferrerFieldName()] = $consent->getReferrerUrl();
            }
            if (null !== $consent->getUsageFieldName()) {
                $attributes[$consent->getUsageFieldName()] = $consent->getUsage();
            }
        }

        return $attributes ?? [];
    }

    /**
     * @throws JsonException
     */
    protected function doSubscribe(string $uri, array $body): bool|int
    {
        try {
            if (!is_string($this->getApiKey())) {
                throw new ApiCredentialsException();
            }

            $bodyStreamed = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

            $request = $this->getRequestFactory()
                ->createRequest('POST', $uri)
                ->withBody($bodyStreamed)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('api-key', $this->getApiKey())
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
            ;

            $res = $this->getClient()->sendRequest($request);

            // https://developers.sendinblue.com/reference/createcontact
            if ($res->getStatusCode() === 200 ||
                $res->getStatusCode() === 201 ||
                $res->getStatusCode() === 204 ||
                $res->getStatusCode() === 400
            ) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if (isset($body['id'])) {
                    return (int) $body['id'];
                }

                if ($res->getStatusCode() === 400 &&
                    isset($body['message']) &&
                    $body['message'] == 'Contact already exist') {
                    return true;
                }
            }
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }

    /**
     * @see https://developers.brevo.com/reference/createcontact
     * @inheritdoc
     */
    public function subscribe(string $email, array $options, array $userConsents = []): bool|int
    {
        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => $this->getListsId(),
            'attributes' => $this->getAttributes($options, $userConsents),
        ];

        return $this->doSubscribe(
            'https://api.brevo.com/v3/contacts',
            $body
        );
    }

    /**
     * @see https://developers.brevo.com/reference/sendtransacemail
     * @inheritdoc
     */
    public function sendTransactionalEmail(array $emails, string|int $emailTemplateId, array $variables = []): string
    {
        if (empty($emails)) {
            throw new \InvalidArgumentException('Emails information missing');
        }

        if (empty($emailTemplateId)) {
            throw new \InvalidArgumentException('Template Id missing');
        }

        if (!is_string($this->getApiKey())) {
            throw new ApiCredentialsException();
        }

        $body = [
            'to' => array_map(function (EmailAddress $emailAddress) {
                return [
                    'email' => $emailAddress->getEmail(),
                    'name' => $emailAddress->getName(),
                ];
            }, $emails),
            'params' => $variables,
            'templateId' => (int) $emailTemplateId,
        ];

        $body = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', 'https://api.brevo.com/v3/smtp/email')
                ->withBody($body)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('api-key', $this->getApiKey());

            $response = $this->getClient()->sendRequest($request);
            return $this->validateResponse($response);
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSendTransactionalEmailException(previous: $exception);
        } catch (ApiResponseException $exception) {
            throw new CannotsendTransactionalEmailException($exception->getResponseBody()['message']);
        }
    }
}
