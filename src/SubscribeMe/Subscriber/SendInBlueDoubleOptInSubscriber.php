<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use SubscribeMe\Exception\CannotSubscribeException;

/**
 * @deprecated Use BrevoDoubleOptInSubscriber instead
 */
class SendInBlueDoubleOptInSubscriber extends SendInBlueSubscriber
{
    private ?int $templateId = null;
    private ?string $redirectionUrl = null;

    /**
     * @param int|null $templateId
     * @return SendInBlueDoubleOptInSubscriber
     */
    public function setTemplateId(?int $templateId): SendInBlueDoubleOptInSubscriber
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * @param string|null $redirectionUrl
     * @return SendInBlueDoubleOptInSubscriber
     */
    public function setRedirectionUrl(?string $redirectionUrl): SendInBlueDoubleOptInSubscriber
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
