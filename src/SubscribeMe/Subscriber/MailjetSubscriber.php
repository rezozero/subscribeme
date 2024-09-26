<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\CannotSendTransactionalEmailException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\MissingApiCredentialsException;
use SubscribeMe\GDPR\UserConsent;
use SubscribeMe\ValueObject\EmailAddress;

class MailjetSubscriber extends AbstractSubscriber
{
    public function getPlatform(): string
    {
        return 'mailjet';
    }

    /**
     * @see https://dev.mailjet.com/email/guides/contact-management/#manage-multiple-contacts-in-a-list
     * @inheritdoc
     */
    public function subscribe(string $email, array $options, array $userConsents = []): bool|int
    {
        if (!is_string($this->getApiKey())) {
            throw new MissingApiCredentialsException();
        }

        if (!is_string($this->getApiSecret())) {
            throw new MissingApiCredentialsException();
        }

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
     * @see https://dev.mailjet.com/email/guides/send-api-v31/#use-templating-language
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
            throw new MissingApiCredentialsException();
        }

        if (!is_string($this->getApiSecret())) {
            throw new MissingApiCredentialsException();
        }

        $body = [
            'Messages' => [[
                'To' => array_map(function (EmailAddress $emailAddress) {
                    return [
                        'email' => $emailAddress->getEmail(),
                        'name' => $emailAddress->getName(),
                    ];
                }, $emails),
                'Variables' => $variables,
                'TemplateID' => (int) $emailTemplateId,
                'TemplateLanguage' => true,
            ]]
        ];

        $body = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', 'https://api.mailjet.com/v3.1/send')
                ->withBody($body)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic ' . base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            return $this->getClient()->sendRequest($request)->getBody()->getContents();
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSendTransactionalEmailException($exception);
        }
    }
}
