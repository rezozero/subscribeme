<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use SubscribeMe\Exception\CannotSubscribeException;
use SubscribeMe\Exception\UnsupportedUnsubscribePlatformException;

final class BrevoDoubleOptInSubscriber extends BrevoSubscriber
{
    private ?int $templateId = null;
    private ?string $redirectionUrl = null;

    public function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function setRedirectionUrl(?string $redirectionUrl): self
    {
        $this->redirectionUrl = $redirectionUrl;
        return $this;
    }

    public function subscribe(string $email, array $options, array $userConsents = []): bool|int
    {
        if (null === $this->templateId) {
            throw new CannotSubscribeException('You must set DOI templateId before subscribing user.');
        }
        if (null === $this->redirectionUrl) {
            throw new CannotSubscribeException('You must set DOI redirectionUrl before subscribing user.');
        }

        // https://developers.sendinblue.com/reference/createdoicontact
        $body = [
            'email' => $email,
            'includeListIds' => $this->getListsId(),
            'attributes' => $this->getAttributes($options, $userConsents),
            'templateId' => $this->templateId,
            'redirectionUrl' => $this->redirectionUrl,
        ];

        return $this->doSubscribe(
            'https://api.brevo.com/v3/contacts/doubleOptinConfirmation',
            $body
        );
    }
}
