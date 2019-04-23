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

class MailjetSubscriber extends AbstractSubscriber
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $apiSecret;
    /** @var string */
    private $contactListId;

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return MailjetSubscriber
     */
    public function setApiKey(string $apiKey): MailjetSubscriber
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    /**
     * @param string $apiSecret
     *
     * @return MailjetSubscriber
     */
    public function setApiSecret(string $apiSecret): MailjetSubscriber
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactListId(): string
    {
        return $this->contactListId;
    }

    /**
     * @param string $contactListId
     *
     * @return MailjetSubscriber
     */
    public function setContactListId(string $contactListId): MailjetSubscriber
    {
        $this->contactListId = $contactListId;

        return $this;
    }

    public function getPlatform(): string
    {
        return 'mailjet';
    }

    public function subscribe(string $email, array $options = [])
    {
        $name = null;
        if (isset($options['Name'])) {
            $name = $options['Name'];
            unset($options['Name']);
        }
        $uri = 'https://api.mailjet.com/v3/REST/contactslist/' . $this->contactListId . '/managecontact';
        try {
            $res = $this->getClient()->request('POST', $uri, [
                'http_errors' => true,
                'auth' => [$this->apiKey, $this->apiSecret],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'Action' => 'addnoforce',
                    'Email' => $email,
                    'Name' => $name,
                    'Properties' => $options,
                ]
            ]);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                $body = json_decode($res->getBody(), true);
                if ($body['Total'] >= 1) {
                    return $body['Data'][0]['ContactID'];
                }
            }
        } catch (RequestException $exception) {
            throw new CannotSubscribeException($exception);
        }

        return false;
    }
}
