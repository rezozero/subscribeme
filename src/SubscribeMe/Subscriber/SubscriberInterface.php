<?php
/**
 * subscribeme - SubscriberInterface.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\Subscriber;

interface SubscriberInterface
{
    /**
     * @return string
     */
    public function getPlatform(): string;

    /**
     * @param string $email
     * @param array  $options
     *
     * @return mixed Contact ID if succeeded or false
     */
    public function subscribe(string $email, array $options = []);
}
