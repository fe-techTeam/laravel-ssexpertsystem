<?php

namespace FeTech\SSExpert\DTOs;

use InvalidArgumentException;

class BulkMessageItem
{
    public readonly string $number;

    public function __construct(
        string $number,
        public readonly string $text,
        public readonly ?string $serviceId = null,
        public readonly ?string $coRelator = null,
        public readonly ?string $linkId = null,
    ) {
        $clean = preg_replace('/\D/', '', $number);
        if (strlen($clean) < 10) {
            throw new InvalidArgumentException("Invalid mobile number format for item [{$number}].");
        }

        $this->number = strlen($clean) > 10 ? substr($clean, -10) : $clean;

        if (trim($this->text) === '') {
            throw new InvalidArgumentException("Message text cannot be empty for number [{$this->number}].");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'] ?? $data['mobile'] ?? $data['phone'] ?? '',
            text: $data['text'] ?? $data['message'] ?? '',
            serviceId: $data['serviceId'] ?? $data['service_id'] ?? null,
            coRelator: $data['coRelator'] ?? $data['co_relator'] ?? null,
            linkId: $data['linkId'] ?? $data['link_id'] ?? null,
        );
    }

    public function toPayload(): array
    {
        return [
            'number' => $this->number,
            'text' => $this->text,
            'serviceId' => $this->serviceId,
            'coRelator' => $this->coRelator,
            'linkId' => $this->linkId,
        ];
    }
}
