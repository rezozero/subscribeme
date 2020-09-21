# Subscribe me

[![Build Status](https://travis-ci.org/rezozero/subscribeme.svg?branch=master)](https://travis-ci.org/rezozero/subscribeme)

Simple mailing-list subscriber factory.

## Supported platforms

- Mailjet
- Mailchimp
- SendInBlue
- YMLP

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

## YMLP options

See https://www.ymlp.com/app/api_command.php?command=Contacts.Add

```php
$subscriber = \SubscribeMe\Factory::createFor('ymlp');
$subscriber->setApiKey('your_username');
$subscriber->setApiSecret('your_api_key');
$subscriber->setContactListId('your_group_id');
// if true the email address will be added even if this person previously 
// unsubscribed or if the email address previously was removed by bounce back handling
$subscriber->setOverruleUnsubscribedBounced(true);
```

For getting your additional fields ID: see https://www.ymlp.com/api/Fields.GetList?Key=api_key&Username=username

## SendInBlue options

See https://developers.sendinblue.com/reference#createcontact

```php
$subscriber = \SubscribeMe\Factory::createFor('sendinblue');
// SendInBlue only requires an API Key
$subscriber->setApiKey('sendinblue_api_key');
// SendInBlue list identifiers are int. You can subscribe user to multiple lists with comma-separated list 
$subscriber->setContactListId('3,5,3'); 
```

For getting your additional fields ID: see https://my.sendinblue.com/lists/add-attributes
