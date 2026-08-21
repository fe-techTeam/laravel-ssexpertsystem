<?php

namespace FeTech\SSExpert;

use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\Contracts\SenderIdServiceInterface;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;

class SSExpertManager
{
    public function __construct(
        protected SmsServiceInterface $smsService,
        protected TemplateServiceInterface $templateService,
        protected BalanceServiceInterface $balanceService,
        protected SenderIdServiceInterface $senderIdService,
        protected GroupServiceInterface $groupService,
    ) {}

    /**
     * Access SMS & OTP service.
     */
    public function sms(): SmsServiceInterface
    {
        return $this->smsService;
    }

    /**
     * Access DLT Template service.
     */
    public function template(): TemplateServiceInterface
    {
        return $this->templateService;
    }

    /**
     * Alias for template().
     */
    public function templates(): TemplateServiceInterface
    {
        return $this->templateService;
    }

    /**
     * Access Balance & Credits service.
     */
    public function balance(): BalanceServiceInterface
    {
        return $this->balanceService;
    }

    /**
     * Access Sender ID (Header) service.
     */
    public function senderId(): SenderIdServiceInterface
    {
        return $this->senderIdService;
    }

    /**
     * Alias for senderId().
     */
    public function senderIds(): SenderIdServiceInterface
    {
        return $this->senderIdService;
    }

    /**
     * Access Contact Group service.
     */
    public function group(): GroupServiceInterface
    {
        return $this->groupService;
    }

    /**
     * Alias for group().
     */
    public function groups(): GroupServiceInterface
    {
        return $this->groupService;
    }

    /**
     * Quick shortcut: Send an OTP SMS.
     */
    public function sendOtp(string $mobile, string $otp, ?string $templateId = null): SmsApiResponse
    {
        return $this->smsService->sendOtp($mobile, $otp, $templateId);
    }

    /**
     * Quick shortcut: Send a single custom SMS.
     */
    public function send(SmsData|array $smsData): SmsApiResponse
    {
        return $this->smsService->send($smsData);
    }

    /**
     * Quick shortcut: Send bulk SMS messages.
     */
    public function sendBulk(BulkSmsData|array $bulkData, ?string $templateId = null): SmsApiResponse
    {
        if (is_array($bulkData)) {
            $bulkData = BulkSmsData::fromArray($bulkData, $templateId);
        }

        return $this->smsService->sendBulk($bulkData);
    }

    /**
     * Quick shortcut: Get available SMS credits.
     */
    public function getCredits(): float
    {
        return $this->balanceService->getCredits();
    }
}
