<?php

declare(strict_types=1);

namespace SubscribeMe\GDPR;

class UserConsent
{
    private ?string $referrerUrl = null;
    private bool $consentGiven = false;
    private ?string $ipAddress = null;
    private ?\DateTime $consentDate = null;
    private ?string $usage = null;
    private ?string $usageFieldName = 'gdpr_consent_usage';
    private ?string $referrerFieldName = 'gdpr_consent_referrer';
    private ?string $ipAddressFieldName = 'gdpr_consent_ip';
    private ?string $consentFieldName = 'gdpr_consent';
    private ?string $dateFieldName = 'gdpr_consent_date';

    /**
     * @return string
     */
    public function getReferrerUrl(): ?string
    {
        return $this->referrerUrl;
    }

    /**
     * @param string|null $referrerUrl
     *
     * @return UserConsent
     */
    public function setReferrerUrl(?string $referrerUrl): UserConsent
    {
        $this->referrerUrl = $referrerUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConsentGiven(): bool
    {
        return $this->consentGiven;
    }

    /**
     * @param bool $consentGiven
     *
     * @return UserConsent
     */
    public function setConsentGiven(bool $consentGiven): UserConsent
    {
        $this->consentGiven = $consentGiven;

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string|null $ipAddress
     *
     * @return UserConsent
     */
    public function setIpAddress(?string $ipAddress): UserConsent
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConsentDate(): ?\DateTime
    {
        return $this->consentDate;
    }

    /**
     * @param \DateTime|null $consentDate
     *
     * @return UserConsent
     */
    public function setConsentDate(?\DateTime $consentDate): UserConsent
    {
        $this->consentDate = $consentDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsage(): ?string
    {
        return $this->usage;
    }

    /**
     * @param string|null $usage
     *
     * @return UserConsent
     */
    public function setUsage(?string $usage): UserConsent
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsageFieldName(): ?string
    {
        return $this->usageFieldName;
    }

    /**
     * @param string|null $usageFieldName
     *
     * @return UserConsent
     */
    public function setUsageFieldName(?string $usageFieldName): UserConsent
    {
        $this->usageFieldName = $usageFieldName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferrerFieldName(): ?string
    {
        return $this->referrerFieldName;
    }

    /**
     * @param string|null $referrerFieldName
     *
     * @return UserConsent
     */
    public function setReferrerFieldName(?string $referrerFieldName): UserConsent
    {
        $this->referrerFieldName = $referrerFieldName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIpAddressFieldName(): ?string
    {
        return $this->ipAddressFieldName;
    }

    /**
     * @param string|null $ipAddressFieldName
     *
     * @return UserConsent
     */
    public function setIpAddressFieldName(?string $ipAddressFieldName): UserConsent
    {
        $this->ipAddressFieldName = $ipAddressFieldName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConsentFieldName(): ?string
    {
        return $this->consentFieldName;
    }

    /**
     * @param string|null $consentFieldName
     *
     * @return UserConsent
     */
    public function setConsentFieldName(?string $consentFieldName): UserConsent
    {
        $this->consentFieldName = $consentFieldName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFieldName(): ?string
    {
        return $this->dateFieldName;
    }

    /**
     * @param string|null $dateFieldName
     *
     * @return UserConsent
     */
    public function setDateFieldName(?string $dateFieldName): UserConsent
    {
        $this->dateFieldName = $dateFieldName;

        return $this;
    }
}
