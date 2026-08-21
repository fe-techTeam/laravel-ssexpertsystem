<?php

namespace FeTech\SSExpert\Facades;

use Illuminate\Support\Facades\Facade;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;

/**
 * @method static SmsApiResponse send(SmsData|array $smsData)
 * @method static SmsApiResponse sendOtp(string $mobile, string $otp, ?string $templateId = null)
 * @method static SmsApiResponse sendBulk(BulkSmsData|array $bulkData)
 * @method static array getMessageStatus(string $messageId)
 * @method static array getDeliveryReport(int $days = 7)
 * @method static array getSmsLogs(int $start = 0, int $length = 50, ?string $fromDate = null, ?string $endDate = null)
 * @method static array getReportSummary(?string $fromDate = null, ?string $endDate = null)
 *
 * @see \FeTech\SSExpert\Services\SSExpertSmsService
 */
class SSExpertSms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SmsServiceInterface::class;
    }
}
