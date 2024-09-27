# Subscribe me

[![Static analysis and code style](https://github.com/rezozero/subscribeme/actions/workflows/run-test.yml/badge.svg)](https://github.com/rezozero/subscribeme/actions/workflows/run-test.yml)

Unified Email Service Library: A simple mailing list subscriber factory that includes a mailing list subscription feature and the ability to send transactional emails.

## Supported platforms

- Mailjet
- Mailchimp
- Brevo (ex SendInBlue)
- Brevo DOI (Double Opt-In) (ex SendInBlue)
- YMLP

## Usage

```
composer require rezozero/subscribeme
```

```php
use SubscribeMe\Factory;

/**
* This library uses PSR18 and PSR17 so you need to provide a client that implements PSR18 like Guzzle for example
 * @param ClientInterface $client
 * @param RequestFactoryInterface $requestFactory
 * @param StreamFactoryInterface $streamFactory
*/
$factory = new Factory($client, $requestFactory, $streamFactory);

// ######## GUZZLE EXAMPLE ##########
$client = new \GuzzleHttp\Client();
$httpFactory = new GuzzleHttp\Psr7\HttpFactory();
$factory = new Factory($client, $httpFactory, $httpFactory);
// ##################################

// 'mailjet' | 'brevo' | 'mailchimp' | 'ymlp'
$subscriber = $factory->createFor('mailjet');

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

/**
 * Method for sending transactional emails (YMLP does not support transactional emails).
 *
 * @param array<\SubscribeMe\ValueObject\EmailAddress> $emails (email required, name optional)
 * @param int|string $emailTemplateId required
 * @param array $variables optional
 */
$subscriber->sendTransactionalEmail($emails, $emailTemplateId, $variables)
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

$subscriber = $factory->createFor('mailchimp');
$subscriber->subscribe(
    'hello@super.test', 
    ['FNAME'=>'Hello', 'LNAME'=>'Super'],
    [$userConsentEmail, $userConsentAds]
);
```

## Mailchimp

### Mailchimp options subscriber

```php
$subscriber = $factory->createFor('mailchimp');
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

### Mailchimp options sender transactional email

See https://mailchimp.com/developer/transactional/api/messages/send-using-message-template/

```php
$subscriber = $factory->createFor('mailchimp');
// Mailchimp only requires an API Key
$subscriber->setApiKey('mailchimp_api_key');
// use an array of value object EmailAddress for recipients
$emails = [
    new \SubscribeMe\ValueObject\EmailAddress('hello@super.test', 'John Doe')
];
// Mailchimp only use string for his $templateEmailId
$emailTemplateId = 'template_name';
/** 
 * MailChimp accepts an array of variables to inject into your transactional template.
*/
$variables = [
    'FNAME' => 'John',
    'LNAME' => 'Doe'
];
$subscriber->sendTransactionalEmail($emails, $emailTemplateId, $variables);
```

## YMLP

### YMLP options subscriber

See https://www.ymlp.com/app/api_command.php?command=Contacts.Add

```php
$subscriber = $factory->createFor('ymlp');
$subscriber->setApiKey('your_username');
$subscriber->setApiSecret('your_api_key');
$subscriber->setContactListId('your_group_id');
// if true the email address will be added even if this person previously 
// unsubscribed or if the email address previously was removed by bounce back handling
$subscriber->setOverruleUnsubscribedBounced(true);
```

For getting your additional fields ID: see https://www.ymlp.com/api/Fields.GetList?Key=api_key&Username=username

### YMLP options sender transactional email

YMLP does not support transactional email, we throw an `UnsupportedTransactionalEmailPlatformException`.

## Brevo

### Brevo subscriber options

See https://developers.brevo.com/reference#createcontact

```php
$subscriber = $factory->createFor('brevo');
// Brevo only requires an API Key
$subscriber->setApiKey('brevo_api_key');
// Brevo list identifiers are int. You can subscribe user to multiple lists with comma-separated list 
$subscriber->setContactListId('3,5,3'); 

$subscriber->subscribe('hello@super.test', ["FNAME" => "Elly", "LNAME" => "Roger"], [$userConsent]);
```

For getting your additional fields ID: see https://my.brevo.com/lists/add-attributes

### Brevo Double Opt-In options

See https://developers.brevo.com/reference/createdoicontact

```php
$subscriber = $factory->createFor('brevo-doi');
// Brevo only requires an API Key
$subscriber->setApiKey('brevo_api_key');
// Brevo list identifiers are int. You can subscribe user to multiple lists with comma-separated list 
$subscriber->setContactListId('3,5,3'); 
$subscriber->setTemplateId(1); 
$subscriber->setRedirectionUrl('https://www.example.com/subscribed');  

$subscriber->subscribe('hello@super.test', ["FNAME" => "Elly", "LNAME" => "Roger"], [$userConsent]);
```

### Brevo sender transactional email options

See https://developers.brevo.com/reference/sendtransacemail

```php
$subscriber = $factory->createFor('brevo');
// Brevo only requires an API Key
$subscriber->setApiKey('brevo_api_key');
// use an array of value object EmailAddress for recipients
$emails = [
    new EmailAddress('jimmy98@example.com', 'Jimmy');
]
// Brevo only use int for his $templateEmailId
$templateEmail = 1;
/** 
 * Brevo accepts an array of variables to inject into your transactional template.
*/
$variables = [
    'FNAME' => 'Joe',
    'LNAME' => 'Doe'
];
$subscriber->sendTransactionalEmail($emails, $templateEmail, $variables);
```

## Mailjet

### Mailjet subscriber options

```php
$subscriber = $factory->createFor('mailjet');
// Mailjet requires an API Key and an API Secret
$subscriber->setApiKey('mailjet_api_key');
$subscriber->setApiSecret('mailjet_api_secret')
// Mailjet list identifiers are int. You can subscribe user to multiple lists with comma-separated list 
$subscriber->setContactListId('3,5,3');

$subscriber->subscribe('hello@super.test', ["FNAME" => "Elly", "LNAME" => "Roger"], [$userConsent]);
```

### Mailjet sender transactional email options

See https://dev.mailjet.com/email/guides/send-api-v31/#use-templating-language

```php
$subscriber = $factory->createFor('mailjet');
// Mailjet requires an API Key and an API Secret
$subscriber->setApiKey('mailjet_api_key');
$subscriber->setApiSecret('mailjet_api_secret')
// use an array of value object EmailAddress for recipients
$emails[] = new EmailAddress('passenger1@mailjet.com', 'passenger 1');
// Mailjet only use int for his $templateEmailId
$templateEmail = 1;
/** 
 * Mailjet accepts an array of variables to inject into your transactional template.
*/
$variables = [
    'day' => 'Tuesday',
    'personalmessage' => 'Happy birthday!'
];
$subscriber->sendTransactionalEmail($emails, $templateEmail, $variables);
```