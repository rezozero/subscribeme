# Subscribe me
Simple mailing-list subscriber factory.

## Supported platforms

- Mailjet
- Mailchimp
- SendInBlue

## Usage

```
composer require rezozero/subscribeme
```

```php
$subscriber = \SubscribeMe\Factory::createFor('mailjet');
$subscriber->setApiKey('xxxx');
$subscriber->setApiSecret('xxxx');
$subscriber->setContactListId('xxxx');

$userConsent = new \SubscribeMe\GDPR\UserConsent();
$userConsent->setReferrerUrl('https://form.test');
$userConsent->setReferrerFieldName('gdpr_consent_referrer');
$userConsent->setConsentGiven(true);
$userConsent->setConsentFieldName('gdpr_consent');
$userConsent->setIpAddress('xx.xx.xx.xx');
$userConsent->setIpAddressFieldName('gdpr_consent_ip_address');
$userConsent->setConsentDate(new \DateTime());
$userConsent->setDateFieldName('gdpr_consent_date');
$userConsent->setUsage('E-mail marketing campaigns');
$userConsent->setUsageFieldName('gdpr_consent_usage');

$subscriber->subscribe('hello@super.test', ['Name' => 'John Doe'], [$userConsent]);
```

## GDPR consent support

Prepare your audience list with additional fields in order to store your users consent (https://www.mailjet.com/gdpr/consent/) :

```php
$userConsent = new \SubscribeMe\GDPR\UserConsent();

$userConsent->setReferrerUrl('https://form.test');
$userConsent->setReferrerFieldName('gdpr_consent_referrer');

$userConsent->setConsentGiven(true);
$userConsent->setConsentFieldName('gdpr_consent');

$userConsent->setIpAddress('xx.xx.xx.xx');
$userConsent->setIpAddressFieldName('gdpr_consent_ip_address');

$userConsent->setConsentDate(new \DateTime());
$userConsent->setDateFieldName('gdpr_consent_date');

$userConsent->setUsage('E-mail marketing campaigns');
$userConsent->setUsageFieldName('gdpr_consent_usage');
```

Some platform already have special mechanism for GDPR consent such as *Mailchimp* : 

```php
$userConsent = new \SubscribeMe\GDPR\UserConsent();

$userConsent->setConsentGiven(true);
// Find your Mailchimp marketing permission ID 
// with a single API call on some existing contacts
$userConsent->setConsentFieldName('e7443e1720');

$userConsent->setIpAddress('xx.xx.xx.xx');
```

You can add multiple `UserConsent` objects when platform allows it.

```php
$userConsentEmail = new \SubscribeMe\GDPR\UserConsent();
$userConsentEmail->setConsentGiven(true);
$userConsentEmail->setConsentFieldName('e7443e1720');
$userConsentEmail->setIpAddress('xx.xx.xx.xx');

$userConsentAds = new \SubscribeMe\GDPR\UserConsent();
$userConsentAds->setConsentGiven(false);
$userConsentAds->setConsentFieldName('other_marketing_id');
$userConsentAds->setIpAddress('xx.xx.xx.xx');

$subscriber = \SubscribeMe\Factory::createFor('mailchimp');
$subscriber->subscribe(
    'hello@super.test', 
    ['FNAME'=>'Hello', 'LNAME'=>'Super'],
    [$userConsentEmail, $userConsentAds]
);
```

## Mailchimp options

```php
$subscriber = \SubscribeMe\Factory::createFor('mailchimp');
$subscriber->setApiKey('your_username');
$subscriber->setApiSecret('xxxx');
$subscriber->setContactListId('xxxx');
// Set you account datacenter
$subscriber->setDc('us19');
// Choose which status your new user will be given
$subscriber->setSubscribed();
// or
$subscriber->setPending();

```
