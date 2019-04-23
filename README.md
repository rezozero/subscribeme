# Subscribe me
Simple mailing-list subscriber factory.

## Supported platforms

- Mailjet

## Usage

```
composer require rezozero/subscribeme
```

```php
$subscriber = \SubscribeMe\Factory::createFor('mailjet');
$subscriber->setApiKey('xxxx');
$subscriber->setApiSecret('xxxx');
$subscriber->setContactListId('xxxx');

$subscriber->subscribe('hello@super.test', ['Name' => 'John Doe']);
```
