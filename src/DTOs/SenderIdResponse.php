<?php

namespace FeTech\SSExpert\DTOs;

use ArrayAccess;
use JsonSerializable;

class SenderIdResponse implements JsonSerializable, ArrayAccess
{
    public function __construct(
        public readonly int $id,
        public readonly string $senderId,
        public readonly int $companyId,
        public readonly bool $isActive,
        public readonly bool $isApproved,
        public readonly ?string $purpose = null,
        public readonly ?string $createdDate = null,
        public readonly ?string $approvalDate = null,
        public readonly bool $isDefault = false,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $approved = $data['IsApproved'] ?? $data['isApproved'] ?? false;
        $isApprovedBool = is_numeric($approved) ? (int) $approved === 1 : (bool) $approved;

        return new self(
            id: (int) ($data['Id'] ?? $data['id'] ?? 0),
            senderId: (string) ($data['SenderId'] ?? $data['senderId'] ?? ''),
            companyId: (int) ($data['CompanyId'] ?? $data['companyId'] ?? 0),
            isActive: (bool) ($data['IsActive'] ?? $data['isActive'] ?? false),
            isApproved: $isApprovedBool,
            purpose: $data['Purpose'] ?? $data['purpose'] ?? null,
            createdDate: $data['CreatedDate'] ?? $data['createdDate'] ?? $data['createDate'] ?? null,
            approvalDate: $data['ApprovalDate'] ?? $data['approvalDate'] ?? $data['approvedDate'] ?? null,
            isDefault: (bool) ($data['IsDefault'] ?? $data['isDefault'] ?? false),
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->senderId,
            'company_id' => $this->companyId,
            'is_active' => $this->isActive,
            'is_approved' => $this->isApproved,
            'purpose' => $this->purpose,
            'created_date' => $this->createdDate,
            'approval_date' => $this->approvalDate,
            'is_default' => $this->isDefault,
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
