<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Client\ClientExceptionInterface;
use SubscribeMe\Exception\ApiCredentialsException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;
use SubscribeMe\GDPR\UserConsent;

class YmlpSubscriber extends AbstractSubscriber
{
    private bool $overruleUnsubscribedBounced = false;

    /**
     * @return bool
     */
    public function isOverruleUnsubscribedBounced(): bool
    {
        return $this->overruleUnsubscribedBounced;
    }

    public function setOverruleUnsubscribedBounced(bool $overruleUnsubscribedBounced): YmlpSubscriber
    {
        $this->overruleUnsubscribedBounced = $overruleUnsubscribedBounced;

        return $this;
    }

    public function getPlatform(): string
    {
        return 'ymlp';
    }

    /**
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

        $params = [
            'Key' => $this->getApiSecret(),
            'Username' => $this->getApiKey(),
            'OverruleUnsubscribedBounced' => $this->overruleUnsubscribedBounced,
            'Email' => $email,
            'GroupID' => ((int) $this->getContactListId()),
            'Output' => 'JSON',
        ];
        /*
         * https://www.ymlp.com/app/api_command.php?command=Contacts.Add
         *
         * Additional fields should be named FieldX…
         *
         * To get your field ID:
         * https://www.ymlp.com/api/Fields.GetList?Key=api_key&Username=username
         */
        if (count($options) > 0) {
            $params = array_merge($params, $options);
        }

        if (count($userConsents) > 0 && null !== $consent = $userConsents[0]) {
            if (!($consent instanceof UserConsent)) {
                throw new \InvalidArgumentException('User consent is not valid UserConsent object');
            }
            if (null !== $consent->getConsentFieldName()) {
                $params[$consent->getConsentFieldName()] = $consent->isConsentGiven();
            }
            if (null !== $consent->getDateFieldName() && null !== $consent->getConsentDate()) {
                $params[$consent->getDateFieldName()] = $consent->getConsentDate()->format('Y-m-d H:i:s');
            }
            if (null !== $consent->getIpAddressFieldName()) {
                $params[$consent->getIpAddressFieldName()] = $consent->getIpAddress();
            }
            if (null !== $consent->getReferrerFieldName()) {
                $params[$consent->getReferrerFieldName()] = $consent->getReferrerUrl();
            }
            if (null !== $consent->getUsageFieldName()) {
                $params[$consent->getUsageFieldName()] = $consent->getUsage();
            }
        }

        $uri = 'https://www.ymlp.com/api/Contacts.Add';
        try {
            $bodyStreamed = $this->getStreamFactory()->createStream(http_build_query($params));
            $request = $this->getRequestFactory()
                ->createRequest('POST', $uri)
                ->withAddedHeader('Content-Type', 'x-www-form-urlencoded')
                ->withAddedHeader('User-Agent', 'rezozero/subscribeme')
                ->withBody($bodyStreamed);

            $res = $this->getClient()->sendRequest($request);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                /** @var array $body */
                $body = json_decode($res->getBody()->getContents(), true);
                if (isset($body['Code']) && $body['Code'] === '0') {
                    return true;
                } elseif (isset($body['Code']) && $body['Code'] === '3') {
                    /*
                     * Do not throw exception if subscriber already exists
                     */
                    return true;
                } elseif (isset($body['Output']) &&
                    $body['Output'] == 'Email address already in selected groups') {
                    /*
                     * Do not throw exception if subscriber already exists
                     */
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
}
