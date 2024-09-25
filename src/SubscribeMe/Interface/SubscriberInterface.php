<?php

declare(strict_types=1);

namespace SubscribeMe\Interface;

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
     *
     * @return bool|int Contact ID if succeeded or false
     */
    public function subscribe(string $email, array $options, array $userConsents = []): bool|int;

    /**
     * @param array<EmailAddress> $emails
     * @param array $variables
     * @param string $templateEmail
     * @return string
     */
    public function sendTransactionalEmail(array $emails, array $variables, string $templateEmail): string;
}
