<?php

namespace FeTech\SSExpert\Exceptions;

class SSExpertAuthException extends SSExpertApiException
{
    public function __construct(
        string $message = 'Invalid or missing SSExpert ApiKey / ClientId credentials.',
        int $code = 401,
        ?int $httpStatus = 401,
        ?array $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $httpStatus, $responseBody, $previous);
    }
}
