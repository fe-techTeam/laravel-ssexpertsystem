<?php

namespace FeTech\SSExpert\Contracts;

use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;

interface SmsServiceInterface
{
    /**
     * Send a single SMS message.
     */
    public function send(SmsData|array $smsData): SmsApiResponse;

    /**
     * Helper to send an OTP SMS with an approved template.
     */
    public function sendOtp(string $mobile, string $otp, ?string $templateId = null): SmsApiResponse;

    /**
     * Send bulk personalized SMS messages in a single API call.
     */
    public function sendBulk(BulkSmsData|array $bulkData): SmsApiResponse;

    /**
     * Query delivery status for a specific message by its Gateway Message ID.
     */
    public function getMessageStatus(string $messageId): array;

    /**
     * Query SMS delivery status logs for recent days.
     */
    public function getDeliveryReport(int $days = 7): array;

    /**
     * Retrieve detailed SMS transmission logs with pagination and date filter.
     */
    public function getSmsLogs(int $start = 0, int $length = 50, ?string $fromDate = null, ?string $endDate = null): array;

    /**
     * Retrieve summary statistics report by date range.
     */
    public function getReportSummary(?string $fromDate = null, ?string $endDate = null): array;
}
