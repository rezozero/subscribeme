<?php
/**
 * subscribeme - MailjetSubscriber.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\GDPR\UserConsent;

class MailchimpSubscriber extends AbstractSubscriber
{
    /** @var string  */
    private $dc = 'us16';
    private $statusWhenSubscribed = 'subscribed';

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

    public function subscribe(string $email, array $options, array $userConsents = [])
    {
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
                return $body['id'];
            }
        } catch (ClientException $exception) {
            $res = $exception->getResponse();
            if (null !== $res && $res->getStatusCode() === 400) {
                $body = json_decode($res->getBody()->getContents(), true);
                if ($body['title'] == 'Member Exists') {
                    /*
                     * Do not throw exception if subscriber already exists
                     */
                    return true;
                }
            }

            throw new CannotSubscribeException($exception->getMessage(), $exception);
        } catch (RequestException $exception) {
            throw new CannotSubscribeException($exception->getMessage(), $exception);
        }

        return false;
    }
}
