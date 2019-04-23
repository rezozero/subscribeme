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
        $uri = 'https://api.mailjet.com/v3/REST/contactslist/' . $this->getContactListId() . '/managecontact';
        try {
            $res = $this->getClient()->request('POST', $uri, [
                'http_errors' => true,
                'auth' => [$this->getApiKey(), $this->getApiSecret()],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'Action' => 'addnoforce',
                    'Email' => $email,
                    'Name' => $name,
                    'Properties' => $options,
                ])
            ]);

            if ($res->getStatusCode() === 200 ||  $res->getStatusCode() === 201) {
                $body = json_decode($res->getBody()->getContents(), true);
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
