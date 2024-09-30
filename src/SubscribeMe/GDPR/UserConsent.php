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

    public function getReferrerUrl(): ?string
    {
        return $this->referrerUrl;
    }

    public function setReferrerUrl(?string $referrerUrl): UserConsent
    {
        $this->referrerUrl = $referrerUrl;

        return $this;
    }

    public function isConsentGiven(): bool
    {
        return $this->consentGiven;
    }

    public function setConsentGiven(bool $consentGiven): UserConsent
    {
        $this->consentGiven = $consentGiven;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): UserConsent
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getConsentDate(): ?\DateTime
    {
        return $this->consentDate;
    }

    public function setConsentDate(?\DateTime $consentDate): UserConsent
    {
        $this->consentDate = $consentDate;

        return $this;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function setUsage(?string $usage): UserConsent
    {
        $this->usage = $usage;

        return $this;
    }

    public function getUsageFieldName(): ?string
    {
        return $this->usageFieldName;
    }

    public function setUsageFieldName(?string $usageFieldName): UserConsent
    {
        $this->usageFieldName = $usageFieldName;

        return $this;
    }

    public function getReferrerFieldName(): ?string
    {
        return $this->referrerFieldName;
    }

    public function setReferrerFieldName(?string $referrerFieldName): UserConsent
    {
        $this->referrerFieldName = $referrerFieldName;

        return $this;
    }

    public function getIpAddressFieldName(): ?string
    {
        return $this->ipAddressFieldName;
    }

    public function setIpAddressFieldName(?string $ipAddressFieldName): UserConsent
    {
        $this->ipAddressFieldName = $ipAddressFieldName;

        return $this;
    }

    public function getConsentFieldName(): ?string
    {
        return $this->consentFieldName;
    }

    public function setConsentFieldName(?string $consentFieldName): UserConsent
    {
        $this->consentFieldName = $consentFieldName;

        return $this;
    }

    public function getDateFieldName(): ?string
    {
        return $this->dateFieldName;
    }

    public function setDateFieldName(?string $dateFieldName): UserConsent
    {
        $this->dateFieldName = $dateFieldName;

        return $this;
    }
}
