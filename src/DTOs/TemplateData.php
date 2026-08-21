<?php

namespace FeTech\SSExpert\DTOs;

use InvalidArgumentException;

class TemplateData
{
    public function __construct(
        public readonly string $templateName,
        public readonly string $messageTemplate,
        public readonly ?string $dltTemplateId = null,
    ) {
        if (trim($this->templateName) === '') {
            throw new InvalidArgumentException('Template name is required.');
        }

        if (trim($this->messageTemplate) === '') {
            throw new InvalidArgumentException('Message template content is required.');
        }
    }

    /**
     * Create instance from associative array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            templateName: $data['templateName'] ?? $data['template_name'] ?? $data['name'] ?? '',
            messageTemplate: $data['messageTemplate'] ?? $data['message_template'] ?? $data['message'] ?? $data['template'] ?? '',
            dltTemplateId: isset($data['templateId']) ? (string) $data['templateId'] : (isset($data['dltTemplateId']) ? (string) $data['dltTemplateId'] : (isset($data['dlt_template_id']) ? (string) $data['dlt_template_id'] : null)),
        );
    }

    /**
     * Convert to payload array for SSExpert API request body.
     */
    public function toPayload(string $apiKey, string $clientId): array
    {
        return [
            'templateName' => $this->templateName,
            'messageTemplate' => $this->messageTemplate,
            'templateId' => $this->dltTemplateId,
            'apiKey' => $apiKey,
            'clientId' => $clientId,
        ];
    }

    /**
     * Convert to standard array.
     */
    public function toArray(): array
    {
        return [
            'template_name' => $this->templateName,
            'message_template' => $this->messageTemplate,
            'dlt_template_id' => $this->dltTemplateId,
        ];
    }
}
