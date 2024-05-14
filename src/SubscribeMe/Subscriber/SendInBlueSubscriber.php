<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\GDPR\UserConsent;

/**
 * @deprecated Use BrevoSubscriber instead
 */
class SendInBlueSubscriber extends AbstractSubscriber
{
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
     * @param string $uri
     * @param array $body
     * @return bool|int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function doSubscribe(string $uri, array $body)
    {
        try {
            $res = $this->getClient()->request('POST', $uri, [
                'http_errors' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api-key' => $this->getApiKey(),
                ],
                'body' => json_encode($body)
            ]);

            // https://developers.sendinblue.com/reference/createcontact
            if ($res->getStatusCode() === 200 ||
                $res->getStatusCode() === 201 ||
                $res->getStatusCode() === 204
            ) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if (isset($body['id'])) {
                    return (int) $body['id'];
                }
            }
        } catch (ClientException $exception) {
            $res = $exception->getResponse();
            /** @var array $body */
            $body = json_decode($res->getBody()->getContents(), true);

            if ($res->getStatusCode() === 400 &&
                isset($body['message']) &&
                $body['message'] == 'Contact already exist') {
                /*
                 * Do not throw exception if subscriber already exists
                 */
                return true;
            }

            if (isset($body['message']) && is_string($body['message'])) {
                throw new CannotSubscribeException($body['message'], $exception);
            }
        } catch (RequestException $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }

    /**
     * @param string $email
     * @param array $options
     * @param UserConsent[] $userConsents
     * @return bool|int Contact ID if succeeded or false
     * @throws GuzzleException
     */
    public function subscribe(string $email, array $options, array $userConsents = [])
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
}
