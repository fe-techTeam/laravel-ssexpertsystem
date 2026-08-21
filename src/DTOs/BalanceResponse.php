<?php

namespace FeTech\SSExpert\DTOs;

use ArrayAccess;
use JsonSerializable;

class BalanceResponse implements JsonSerializable, ArrayAccess
{
    public function __construct(
        public readonly string $pluginType,
        public readonly string $rawCredits,
        public readonly float $credits,
        public readonly ?string $currencySymbol = null,
        public readonly array $raw = [],
    ) {}

    /**
     * Factory method supporting PascalCase and camelCase.
     */
    public static function fromArray(array $data): self
    {
        $rawCreditStr = (string) ($data['Credits'] ?? $data['credits'] ?? $data['TotalCredits'] ?? $data['totalCredits'] ?? '0');
        // Extracts numerical balance from strings like "credit17935.000000" or "17935"
        preg_match('/[0-9]+(\.[0-9]+)?/', $rawCreditStr, $matches);
        $credits = isset($matches[0]) ? (float) $matches[0] : 0.0;

        return new self(
            pluginType: (string) ($data['PluginType'] ?? $data['pluginType'] ?? $data['ProductTypeName'] ?? $data['productTypeName'] ?? 'SMS'),
            rawCredits: $rawCreditStr,
            credits: $credits,
            currencySymbol: $data['CurrencySymbol'] ?? $data['currencySymbol'] ?? null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'plugin_type' => $this->pluginType,
            'credits' => $this->credits,
            'raw_credits' => $this->rawCredits,
            'currency_symbol' => $this->currencySymbol,
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
