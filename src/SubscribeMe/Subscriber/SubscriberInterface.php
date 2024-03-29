<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use SubscribeMe\GDPR\UserConsent;

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
     * @return mixed Contact ID if succeeded or false
     */
    public function subscribe(string $email, array $options, array $userConsents = []);
}
