<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use JsonException;
use SubscribeMe\Exception\UnsupportedTransactionalEmailPlatformException;
use SubscribeMe\Exception\UnsupportedUnsubscribePlatformException;
use SubscribeMe\GDPR\UserConsent;
use SubscribeMe\ValueObject\EmailAddress;

interface SubscriberInterface
{
    public function getPlatform(): string;

    public function setApiSecret(?string $apiSecret): SubscriberInterface;

    public function setApiKey(?string $apiKey): SubscriberInterface;

    public function setContactListId(?string $contactListId): SubscriberInterface;

    /**
     * @param string      $email
     * @param array       $options
     * @param UserConsent[] $userConsents
     * @return bool|int Contact ID if succeeded or false
     * @throws JsonException
     */
    public function subscribe(string $email, array $options, array $userConsents = []): bool|int;

    /**
     * @param string $email
     * @return bool true on succeeded or false
     * @throws JsonException|UnsupportedUnsubscribePlatformException
     */
    public function unsubscribe(string $email): bool;

    /**
     * @param array<EmailAddress> $emails
     * @param string|int $emailTemplateId
     * @param array<string, string|int|bool|array<string|int|bool>> $variables
     * @return string Platform Response body after sending
     * @throws JsonException|UnsupportedTransactionalEmailPlatformException
     */
    public function sendTransactionalEmail(array $emails, string|int $emailTemplateId, array $variables = []): string;
}
