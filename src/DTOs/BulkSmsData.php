<?php

namespace FeTech\SSExpert\DTOs;

use InvalidArgumentException;

class BulkSmsData
{
    /** @var array<int, BulkMessageItem> */
    public readonly array $messageParameters;

    /**
     * @param  array<int|string, BulkMessageItem|array|string>  $messages
     */
    public function __construct(
        array $messages,
        public readonly ?string $templateId = null,
        public readonly ?string $senderId = null,
        public readonly ?string $principleEntityId = null,
        public readonly ?string $scheduleDateTime = null,
        public readonly bool $isUnicode = false,
        public readonly bool $isFlash = false,
        public readonly bool $isRegisteredForDelivery = true,
        public readonly int $dataCoding = 0,
    ) {
        if (empty($messages)) {
            throw new InvalidArgumentException('Bulk messages list cannot be empty.');
        }

        $items = [];
        foreach ($messages as $key => $item) {
            if ($item instanceof BulkMessageItem) {
                $items[] = $item;
            } elseif (is_array($item)) {
                $items[] = BulkMessageItem::fromArray($item);
            } elseif (is_string($item)) {
                $items[] = new BulkMessageItem(number: (string) $key, text: $item);
            }
        }

        if (empty($items)) {
            throw new InvalidArgumentException('No valid message items provided for bulk dispatch.');
        }

        $this->messageParameters = $items;
    }

    public static function fromArray(array $data, ?string $defaultTemplateId = null): self
    {
        $rawMessages = $data['messages'] ?? $data['messageParameters'] ?? $data['message_parameters'] ?? null;

        if ($rawMessages === null) {
            $isAssocMap = true;
            foreach (array_keys($data) as $k) {
                if (in_array($k, ['templateId', 'template_id', 'senderId', 'sender_id', 'principleEntityId', 'peid', 'isUnicode', 'isFlash'])) {
                    $isAssocMap = false;
                    break;
                }
            }

            if ($isAssocMap && ! empty($data)) {
                $rawMessages = $data;
            } else {
                $rawMessages = [];
            }
        }

        $templateId = $data['templateId'] ?? $data['template_id'] ?? $defaultTemplateId;

        return new self(
            messages: is_array($rawMessages) ? $rawMessages : [],
            templateId: $templateId !== null ? (string) $templateId : null,
            senderId: $data['senderId'] ?? $data['sender_id'] ?? null,
            principleEntityId: $data['principleEntityId'] ?? $data['principle_entity_id'] ?? $data['peid'] ?? null,
            scheduleDateTime: $data['scheduleDateTime'] ?? $data['schedule_date_time'] ?? null,
            isUnicode: (bool) ($data['isUnicode'] ?? $data['is_unicode'] ?? false),
            isFlash: (bool) ($data['isFlash'] ?? $data['is_flash'] ?? false),
            isRegisteredForDelivery: (bool) ($data['isRegisteredForDelivery'] ?? $data['is_registered_for_delivery'] ?? true),
            dataCoding: (int) ($data['dataCoding'] ?? $data['data_coding'] ?? 0),
        );
    }

    public function toPayload(string $apiKey, string $clientId, ?string $defaultSenderId = null, ?string $defaultPeid = null): array
    {
        return [
            'senderId' => $this->senderId ?: $defaultSenderId,
            'isUnicode' => $this->isUnicode,
            'isFlash' => $this->isFlash,
            'isRegisteredForDelivery' => $this->isRegisteredForDelivery,
            'validityPeriod' => null,
            'dataCoding' => $this->dataCoding,
            'scheduleDateTime' => $this->scheduleDateTime,
            'principleEntityId' => $this->principleEntityId ?: $defaultPeid,
            'templateId' => $this->templateId,
            'messageParameters' => array_map(fn (BulkMessageItem $item) => $item->toPayload(), $this->messageParameters),
            'apiKey' => $apiKey,
            'clientId' => $clientId,
        ];
    }
}
