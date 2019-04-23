<?php
/**
 * subscribeme - MailjetSubscriber.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use GuzzleHttp\Exception\RequestException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\GDPR\UserConsent;

class MailjetSubscriber extends AbstractSubscriber
{
    public function getPlatform(): string
    {
        return 'mailjet';
    }

    public function subscribe(string $email, array $options, array $userConsents = [])
    {
        $name = null;
        if (isset($options['Name'])) {
            $name = $options['Name'];
            unset($options['Name']);
        }
        $body = [
            'Action' => 'addnoforce',
            'Email' => $email,
            'Name' => $name,
            'Properties' => $options,
        ];

        if (count($userConsents) > 0 && null !== $consent = $userConsents[0]) {
            if (!($consent instanceof UserConsent)) {
                throw new \InvalidArgumentException('User consent is not valid UserConsent object');
            }
            if (null !== $consent->getConsentFieldName()) {
                $body['Properties'][$consent->getConsentFieldName()] = $consent->isConsentGiven();
            }
            if (null !== $consent->getDateFieldName()) {
                $body['Properties'][$consent->getDateFieldName()] = $consent->getConsentDate()->format('Y-m-d H:i:s');
            }
            if (null !== $consent->getIpAddressFieldName()) {
                $body['Properties'][$consent->getIpAddressFieldName()] = $consent->getIpAddress();
            }
            if (null !== $consent->getReferrerFieldName()) {
                $body['Properties'][$consent->getReferrerFieldName()] = $consent->getReferrerUrl();
            }
            if (null !== $consent->getUsageFieldName()) {
                $body['Properties'][$consent->getUsageFieldName()] = $consent->getUsage();
            }
        }

        $uri = 'https://api.mailjet.com/v3/REST/contactslist/' . $this->getContactListId() . '/managecontact';
        try {
            $res = $this->getClient()->request('POST', $uri, [
                'http_errors' => true,
                'auth' => [$this->getApiKey(), $this->getApiSecret()],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($body)
            ]);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['Total'] >= 1) {
                    return $body['Data'][0]['ContactID'];
                }
            }
        } catch (RequestException $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }
}
