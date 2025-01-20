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

### Symfony usage

With Symfony, you don't have to use the `Factory`,
you can directly make your code generic by depending on `SubscriberInterface`,
which means that if you want to change platform later,
you will just have to change the registration.

```php
final class YourClass
{
    public function __construct(
        // Make it generic, let Symfony provide the right service for you
        private SubscriberInterface $subscriber
    ) {
    }
    
    public function sendTransactional()
    {
        $this->subscriber->sendTransactionalEmail(
            [
                new EmailAddress('user@example.com')
            ],
            $templateId
        )
    }
}
```

```yaml
# services.yaml
services:
  SubscribeMe\Subscriber\SubscriberInterface:
    # Here register the platform class used in your project (example with Mailjet)
    class: SubscribeMe\Subscriber\MailjetSubscriber
    # Here comes the Symfony magic, PSR17 and PSR18 will be automatically provided
    autowire: true
    calls:
      # Here call necessary methods according to your platform (Mailjet need apiKey and apiSecret)
      - setApiKey: [ '%env(string:APP_MAILJET_API_KEY)%' ]
      - setApiSecret: [ '%env(string:APP_MAILJET_API_SECRET_KEY)%' ]
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


## OxiMailing

### OxiMailing subscriber options

```php
$subscriber = $factory->createFor('oximailing');
// OxiMailing requires an API Key and an API Secret
$subscriber->setApiKey('oximailing_api_key');
$subscriber->setApiSecret('oximailing_api_secret')
// OxiMailing list identifiers are int. You can only subscribe user to one list
$subscriber->setContactListId('123');
// OxiMailing Accept 3 modes
//Allows you to explain what to do with new duplicates :
//- ignore : remove duplicates
//- insert : don't do anything (all contacts are imported even duplicates)
//- update : update existing contacts information rather than adding duplicates
$subscriber->subscribe('hello@super.test', ['mode' => 'update']);
```

### OxiMailing sender transactional email options

OxiMailing does not support transactional email, we throw an `UnsupportedTransactionalEmailPlatformException`.