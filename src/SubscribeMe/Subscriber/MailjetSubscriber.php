<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\GDPR\UserConsent;
use SubscribeMe\ValueObject\EmailAddress;

class MailjetSubscriber extends AbstractSubscriber
{
    public function getPlatform(): string
    {
        return 'mailjet';
    }

    public function subscribe(string $email, array $options, array $userConsents = []): bool|int
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
            if (null !== $consent->getDateFieldName() && null !== $consent->getConsentDate()) {
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
            if (!is_string(json_encode($body))) {
                throw new \InvalidArgumentException('Body missing');
            }
            $bodyStreamed = $this->getStreamFactory()->createStream(json_encode($body));

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
     * @param array<EmailAddress> $emails
     * @param array $variables
     * @param string $templateEmail
     * @return string
     */
    public function sendTransactionalEmail(array $emails, array $variables, string $templateEmail): string
    {
        $recipients = array_map(function (EmailAddress $emailAddress) {
            return [
                'email' => $emailAddress->getEmail(),
                'name' => $emailAddress->getName(),
            ];
        }, $emails);

        if (empty($emails)) {
            throw new \InvalidArgumentException('Emails information missing');
        }

        if (empty($variables)) {
            throw new \InvalidArgumentException('Variables missing');
        }

        if (empty($templateEmail)) {
            throw new \InvalidArgumentException('Template Id missing');
        }

        if (!is_string($this->getApiKey())) {
            throw new \InvalidArgumentException('ApiKey is not a string');
        }

        if (!is_string($this->getApiSecret())) {
            throw new \InvalidArgumentException('ApiSecret is not a string');
        }

        $body = [
            'Messages' => [[
                'To' => $recipients,
                'Variables' => $variables,
                'TemplateID' => $templateEmail,
                'TemplateLanguage' => true,
            ]]
        ];

        if (!is_string(json_encode($body))) {
            throw new \InvalidArgumentException('Body missing');
        }
        $body = $this->getStreamFactory()->createStream(json_encode($body));

        $url = 'https://api.mailjet.com/v3.1/send';

        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', $url)
                ->withBody($body)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic ' . base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            $response = $this->getClient()->sendRequest($request);

            return $response->getBody()->getContents();
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSendTransactionalEmailException($exception);
        }
    }
}
