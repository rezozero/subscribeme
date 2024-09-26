<?php

declare(strict_types=1);

namespace SubscribeMe\Subscriber;

use Psr\Http\Message\ResponseInterface;
use SubscribeMe\Exception\ApiResponseException;

trait ResponseValidationTrait
{
    protected function validateResponse(ResponseInterface $response): string
    {
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return $response->getBody()->getContents();
        }
        /** @var array $result */
        $result = json_decode($response->getBody()->getContents(), true);
        throw new ApiResponseException($result);
    }
}
