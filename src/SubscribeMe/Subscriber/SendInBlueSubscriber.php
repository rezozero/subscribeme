<?php
declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\GDPR\UserConsent;

class SendInBlueSubscriber extends AbstractSubscriber
{
    public function getPlatform(): string
    {
        return 'sendinblue';
    }

    public function subscribe(string $email, array $options, array $userConsents = [])
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

        $body = [
            'updateEnabled' => true,
            'email' => $email,
            'listIds' => $listIds
        ];

        if (count($options) > 0) {
            $body['attributes'] = $options;
        }

        if (count($userConsents) > 0 && null !== $consent = $userConsents[0]) {
            if (!isset($body['attributes'])) {
                $body['attributes'] = [];
            }
            if (!($consent instanceof UserConsent)) {
                throw new \InvalidArgumentException('User consent is not valid UserConsent object');
            }
            if (null !== $consent->getConsentFieldName()) {
                $body['attributes'][$consent->getConsentFieldName()] = $consent->isConsentGiven();
            }
            if (null !== $consent->getDateFieldName() && null !== $consent->getConsentDate()) {
                $body['attributes'][$consent->getDateFieldName()] = $consent->getConsentDate()->format('Y-m-d H:i:s');
            }
            if (null !== $consent->getIpAddressFieldName()) {
                $body['attributes'][$consent->getIpAddressFieldName()] = $consent->getIpAddress();
            }
            if (null !== $consent->getReferrerFieldName()) {
                $body['attributes'][$consent->getReferrerFieldName()] = $consent->getReferrerUrl();
            }
            if (null !== $consent->getUsageFieldName()) {
                $body['attributes'][$consent->getUsageFieldName()] = $consent->getUsage();
            }
        }

        $uri = 'https://api.sendinblue.com/v3/contacts';
        try {
            $res = $this->getClient()->request('POST', $uri, [
                'http_errors' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api-key' => $this->getApiKey(),
                ],
                'body' => json_encode($body)
            ]);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                $body = json_decode($res->getBody()->getContents(), true);
                if (isset($body['id'])) {
                    return $body['id'];
                }
            }
        } catch (ClientException $exception) {
            $res = $exception->getResponse();
            if (null !== $res) {
                $body = json_decode($res->getBody()->getContents(), true);

                if ($res->getStatusCode() === 400 &&
                    isset($body['message']) &&
                    $body['message'] == 'Contact already exist') {
                    /*
                     * Do not throw exception if subscriber already exists
                     */
                    return true;
                }

                if (isset($body['message'])) {
                    throw new CannotSubscribeException($body['message'], $exception);
                }
            }
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        } catch (RequestException $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }
}
