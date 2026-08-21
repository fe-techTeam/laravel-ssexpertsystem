<?php

namespace FeTech\SSExpert\DTOs;

use InvalidArgumentException;

class SmsData
{
    public readonly string $mobileNumbers;

    public function __construct(
        string $mobileNumbers,
        public readonly string $message,
        public readonly ?string $templateId = null,
        public readonly ?string $senderId = null,
        public readonly ?string $principleEntityId = null,
        public readonly bool $isUnicode = false,
        public readonly bool $isFlash = false,
        public readonly bool $isRegisteredForDelivery = true,
        public readonly int $dataCoding = 0,
        public readonly ?string $schedTime = null,
        public readonly ?string $serviceId = null,
        public readonly ?string $coRelator = null,
        public readonly ?string $linkId = null,
        public readonly ?string $groupId = null,
    ) {
        $cleanMobile = preg_replace('/\D/', '', $mobileNumbers);
        if (strlen($cleanMobile) < 10) {
            throw new InvalidArgumentException('Invalid phone number format: must contain at least 10 digits.');
        }

        // Standardize to last 10 digits for Indian numbers if prefixed with 91 or 0
        $this->mobileNumbers = strlen($cleanMobile) > 10 ? substr($cleanMobile, -10) : $cleanMobile;

        if (trim($this->message) === '') {
            throw new InvalidArgumentException('SMS message content cannot be empty.');
        }
    }

    /**
     * Create from associative array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mobileNumbers: $data['mobileNumbers'] ?? $data['mobile_numbers'] ?? $data['mobile'] ?? $data['number'] ?? $data['phone'] ?? '',
            message: $data['message'] ?? $data['text'] ?? $data['msg'] ?? '',
            templateId: isset($data['templateId']) ? (string) $data['templateId'] : (isset($data['template_id']) ? (string) $data['template_id'] : null),
            senderId: $data['senderId'] ?? $data['sender_id'] ?? $data['sender'] ?? null,
            principleEntityId: $data['principleEntityId'] ?? $data['principle_entity_id'] ?? $data['peid'] ?? null,
            isUnicode: (bool) ($data['isUnicode'] ?? $data['is_unicode'] ?? false),
            isFlash: (bool) ($data['isFlash'] ?? $data['is_flash'] ?? false),
            isRegisteredForDelivery: (bool) ($data['isRegisteredForDelivery'] ?? $data['is_registered_for_delivery'] ?? true),
            dataCoding: (int) ($data['dataCoding'] ?? $data['data_coding'] ?? 0),
            schedTime: $data['schedTime'] ?? $data['sched_time'] ?? null,
            serviceId: $data['serviceId'] ?? $data['service_id'] ?? null,
            coRelator: $data['coRelator'] ?? $data['co_relator'] ?? null,
            linkId: $data['linkId'] ?? $data['link_id'] ?? null,
            groupId: $data['groupId'] ?? $data['group_id'] ?? null,
        );
    }

    /**
     * Generate API payload formatted for SSExpert /api/v2/SendSMS.
     */
    public function toPayload(string $apiKey, string $clientId, ?string $defaultSenderId = null, ?string $defaultPeid = null): array
    {
        return [
            'senderId' => $this->senderId ?: $defaultSenderId,
            'is_Unicode' => $this->isUnicode,
            'is_Flash' => $this->isFlash,
            'isRegisteredForDelivery' => $this->isRegisteredForDelivery,
            'validityPeriod' => null,
            'dataCoding' => $this->dataCoding,
            'schedTime' => $this->schedTime,
            'groupId' => $this->groupId,
            'message' => $this->message,
            'mobileNumbers' => $this->mobileNumbers,
            'serviceId' => $this->serviceId,
            'coRelator' => $this->coRelator,
            'linkId' => $this->linkId,
            'principleEntityId' => $this->principleEntityId ?: $defaultPeid,
            'templateId' => $this->templateId,
            'apiKey' => $apiKey,
            'clientId' => $clientId,
        ];
    }
}
