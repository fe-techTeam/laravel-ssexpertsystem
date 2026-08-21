<?php

namespace FeTech\SSExpert\Exceptions;

use Exception;
use Throwable;

class SSExpertApiException extends Exception
{
    public function __construct(
        string $message = 'SSExpert API error',
        int $code = 0,
        public readonly ?int $httpStatus = null,
        public readonly ?array $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
