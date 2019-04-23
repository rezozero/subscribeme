<?php
/**
 * subscribeme - CannotSubscribeException.php
 *
 * Initial version by: ambroisemaupate
 * Initial version created on: 2019-04-23
 */
declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

class CannotSubscribeException extends \RuntimeException
{
    /**
     * CannotSubscribeException constructor.
     *
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null) {
        parent::__construct('Cannot subscribe email to platform', 0, $previous);
    }
}
