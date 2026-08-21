<?php

namespace FeTech\SSExpert\DTOs;

use ArrayAccess;
use JsonSerializable;

class GroupResponse implements JsonSerializable, ArrayAccess
{
    public function __construct(
        public readonly int $groupId,
        public readonly string $groupName,
        public readonly int $contactCount,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            groupId: (int) ($data['GroupId'] ?? $data['groupId'] ?? $data['id'] ?? 0),
            groupName: (string) ($data['GroupName'] ?? $data['groupName'] ?? $data['name'] ?? ''),
            contactCount: (int) ($data['ContactCount'] ?? $data['contactCount'] ?? $data['count'] ?? 0),
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'contact_count' => $this->contactCount,
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
