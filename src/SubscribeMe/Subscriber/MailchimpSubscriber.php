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

class MailchimpSubscriber extends AbstractSubscriber
{
    use ResponseValidationTrait;
    private string $dc = 'us16';
    private string $statusWhenSubscribed = 'subscribed';

    public function getPlatform(): string
    {
        return 'mailchimp';
    }

    /**
     * @return string
     */
    public function getDc(): string
    {
        return $this->dc;
    }

    /**
     * @param string $dc
     *
     * @return MailchimpSubscriber
     */
    public function setDc(string $dc): MailchimpSubscriber
    {
        $this->dc = $dc;

        return $this;
    }

    /**
     * @return MailchimpSubscriber
     */
    public function setSubscribed(): MailchimpSubscriber
    {
        $this->statusWhenSubscribed = 'subscribed';
        return $this;
    }

    /**
     * @return MailchimpSubscriber
     */
    public function setPending(): MailchimpSubscriber
    {
        $this->statusWhenSubscribed = 'pending';
        return $this;
    }

    /**
     * @see https://mailchimp.com/developer/marketing/api/list-members/add-member-to-list/
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

        $uri = 'https://' . $this->getDc() . '.api.mailchimp.com/3.0/lists/' . $this->getContactListId() . '/members';
        $body = [
            'status' => $this->statusWhenSubscribed,
            'email_address' => $email,
        ];
        if (count($options) > 0) {
            $body['merge_fields'] = $options;
        }

        if (count($userConsents) > 0) {
            $body['marketing_permissions'] = [];
            foreach ($userConsents as $consent) {
                if (!($consent instanceof UserConsent)) {
                    throw new \InvalidArgumentException('User consent is not valid UserConsent object');
                }

                if (null !== $consent->getIpAddress()) {
                    $body['ip_signup'] = $consent->getIpAddress();
                }
                if (null !== $consent->getConsentFieldName()) {
                    $body['marketing_permissions'][] = [
                        'marketing_permission_id' => $consent->getConsentFieldName(),
                        'enabled' => $consent->isConsentGiven(),
                    ];
                }
            }
        }

        try {
            $bodyStreamed = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

            $request = $this->getRequestFactory()
                ->createRequest('POST', $uri)
                ->withBody($bodyStreamed)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withAddedHeader('Authorization', 'Basic '.base64_encode(sprintf('%s:%s', $this->getApiKey(), $this->getApiSecret())));

            $res = $this->getClient()->sendRequest($request);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201 || $res->getStatusCode() === 400) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['title'] == 'Member Exists') {
                    /*
                     * Do not throw exception if subscriber already exists
                     */
                    return true;
                }
                if ($body['id'] !== null) {
                    return $body['id'];
                }
                return false;
            }
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }

    /**
     * @see https://mailchimp.com/developer/transactional/api/messages/send-using-message-template/
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

        if (!empty($variables)) {
            $variables = array_map(function ($variable) {
                return [
                    'name' => $variable['name'],
                    'content' => $variable['content'],
                ];
            }, $variables);
        }

        $body = [
            'template_name' => (string) $emailTemplateId,
            'template_content' => [],
            'message' => [
                'to' => array_map(function (EmailAddress $emailAddress) {
                    return [
                        'email' => $emailAddress->getEmail(),
                        'name' => $emailAddress->getName(),
                        'type' => 'to'
                    ];
                }, $emails),
                'global_merge_vars' => $variables
            ],
            'key' => $this->getApiKey(),
        ];

        $body = $this->getStreamFactory()->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', 'https://mandrillapp.com/api/1.0/messages/send-template')
                ->withBody($body)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme');

            $response = $this->getClient()->sendRequest($request);
            return $this->validateResponse($response);
        } catch (ClientExceptionInterface $exception) {
            throw new CannotSendTransactionalEmailException(previous: $exception);
        } catch (ApiResponseException $exception) {
            throw new CannotsendTransactionalEmailException($exception->getResponseBody()['message']);
        }
    }
}
