<?php
/**
 * subscribeme - UserConsent.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\GDPR;

class UserConsent
{
    /** @var string */
    private $referrerUrl;
    /** @var boolean */
    private $consentGiven = false;
    /** @var string */
    private $ipAddress;
    /** @var \DateTime */
    private $consentDate;
    /** @var string */
    private $usage;
    /** @var string|null */
    private $usageFieldName = 'gdpr_consent_usage';
    /** @var string|null */
    private $referrerFieldName = 'gdpr_consent_referrer';
    /** @var string|null */
    private $ipAddressFieldName = 'gdpr_consent_ip';
    /** @var string|null */
    private $consentFieldName = 'gdpr_consent';
    /** @var string|null */
    private $dateFieldName = 'gdpr_consent_date';

    /**
     * @return string
     */
    public function getReferrerUrl(): ?string
    {
        return $this->referrerUrl;
    }

    /**
     * @param string $referrerUrl
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
     * @param string $ipAddress
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
     * @param \DateTime $consentDate
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
     * @param string $usage
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
