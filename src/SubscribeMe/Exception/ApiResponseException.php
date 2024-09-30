<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class ApiResponseException extends \RuntimeException
{
    public function __construct(private array $responseBody, Throwable $previous = null)
    {
        parent::__construct('Api response error', 0, $previous);
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}
