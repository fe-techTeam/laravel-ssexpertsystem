<?php

namespace FeTech\SSExpert\DTOs;

use ArrayAccess;
use JsonSerializable;

class TemplateResponse implements JsonSerializable, ArrayAccess
{
    public function __construct(
        public readonly int $templateId,
        public readonly int $companyId,
        public readonly string $templateName,
        public readonly string $messageTemplate,
        public readonly bool $isApproved,
        public readonly bool $isActive,
        public readonly ?string $productName = null,
        public readonly ?string $createDate = null,
        public readonly ?string $createDateString = null,
        public readonly ?string $approvedDate = null,
        public readonly ?string $approvedDateString = null,
        public readonly ?string $dltTemplateId = null,
    ) {}

    /**
     * Factory method supporting PascalCase, camelCase, and snake_case API responses.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            templateId: (int) ($data['TemplateId'] ?? $data['templateId'] ?? $data['template_id'] ?? $data['id'] ?? 0),
            companyId: (int) ($data['CompanyId'] ?? $data['companyId'] ?? $data['company_id'] ?? 0),
            templateName: (string) ($data['TemplateName'] ?? $data['templateName'] ?? $data['template_name'] ?? $data['name'] ?? ''),
            messageTemplate: (string) ($data['MessageTemplate'] ?? $data['messageTemplate'] ?? $data['message_template'] ?? $data['message'] ?? ''),
            isApproved: (bool) ($data['IsApproved'] ?? $data['isApproved'] ?? $data['is_approved'] ?? false),
            isActive: (bool) ($data['IsActive'] ?? $data['isActive'] ?? $data['is_active'] ?? false),
            productName: $data['ProductName'] ?? $data['productName'] ?? $data['product_name'] ?? null,
            createDate: $data['CreateDate'] ?? $data['CreatededDate'] ?? $data['createDate'] ?? $data['create_date'] ?? null,
            createDateString: $data['CreateDateString'] ?? $data['CreatededDate'] ?? $data['createDateString'] ?? $data['create_date_string'] ?? null,
            approvedDate: $data['ApprovedDate'] ?? $data['approvedDate'] ?? $data['approved_date'] ?? null,
            approvedDateString: $data['ApprovedDateString'] ?? $data['ApprovedDate'] ?? $data['approvedDateString'] ?? $data['approved_date_string'] ?? null,
            dltTemplateId: isset($data['DltTemplateId']) ? (string) $data['DltTemplateId'] : (isset($data['dltTemplateId']) ? (string) $data['dltTemplateId'] : (isset($data['dlt_template_id']) ? (string) $data['dlt_template_id'] : null)),
        );
    }

    /**
     * Convert to array representation.
     */
    public function toArray(): array
    {
        return [
            'template_id' => $this->templateId,
            'company_id' => $this->companyId,
            'template_name' => $this->templateName,
            'message_template' => $this->messageTemplate,
            'is_approved' => $this->isApproved,
            'is_active' => $this->isActive,
            'product_name' => $this->productName,
            'create_date' => $this->createDate,
            'create_date_string' => $this->createDateString,
            'approved_date' => $this->approvedDate,
            'approved_date_string' => $this->approvedDateString,
            'dlt_template_id' => $this->dltTemplateId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {}

    public function offsetUnset(mixed $offset): void {}
}
