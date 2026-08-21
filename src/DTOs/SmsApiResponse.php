<?php

namespace FeTech\SSExpert\DTOs;

use ArrayAccess;
use JsonSerializable;

class SmsApiResponse implements JsonSerializable, ArrayAccess
{
    public function __construct(
        public readonly int $errorCode,
        public readonly ?string $errorDescription = null,
        public readonly mixed $data = null,
        public readonly array $raw = [],
    ) {}

    /**
     * Determine if SMS request was accepted.
     */
    public function isSuccess(): bool
    {
        if ($this->errorCode !== 0) {
            return false;
        }

        // If data contains individual message result objects
        if (is_array($this->data) && isset($this->data[0])) {
            $first = $this->data[0];
            if (is_array($first)) {
                $itemErrorCode = $first['MessageErrorCode'] ?? $first['messageErrorCode'] ?? $first['errorCode'] ?? 0;

                return (int) $itemErrorCode === 0;
            }
        }

        return true;
    }

    /**
     * Return primary Message ID if present.
     */
    public function getMessageId(): ?string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        if (is_array($this->data) && isset($this->data[0]) && is_array($this->data[0])) {
            return $this->data[0]['MessageId'] ?? $this->data[0]['messageId'] ?? null;
        }

        return null;
    }

    /**
     * Get error description if failed.
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccess()) {
            return null;
        }

        if ($this->errorDescription) {
            return $this->errorDescription;
        }

        if (is_array($this->data) && isset($this->data[0]) && is_array($this->data[0])) {
            return $this->data[0]['MessageErrorDescription'] ?? $this->data[0]['messageErrorDescription'] ?? null;
        }

        return 'Failed to send SMS.';
    }

    /**
     * Factory from API response array.
     */
    public static function fromArray(array $response): self
    {
        return new self(
            errorCode: (int) ($response['errorCode'] ?? $response['ErrorCode'] ?? $response['error_code'] ?? 0),
            errorDescription: $response['errorDescription'] ?? $response['ErrorDescription'] ?? $response['error_description'] ?? null,
            data: $response['data'] ?? $response['Data'] ?? null,
            raw: $response,
        );
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'error_description' => $this->errorDescription,
            'message_id' => $this->getMessageId(),
            'data' => $this->data,
            'success' => $this->isSuccess(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->raw[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->raw[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {}

    public function offsetUnset(mixed $offset): void {}
}
