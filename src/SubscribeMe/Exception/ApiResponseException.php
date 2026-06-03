<?php

declare(strict_types=1);

namespace SubscribeMe\Exception;

use Throwable;

final class ApiResponseException extends \RuntimeException
{
    public function __construct(private array $responseBody, ?Throwable $previous = null, private ?int $statusCode = null)
    {
        parent::__construct('Api response error', 0, $previous);
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
