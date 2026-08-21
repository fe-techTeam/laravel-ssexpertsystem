<?php

namespace FeTech\SSExpert\Facades;

use Illuminate\Support\Facades\Facade;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\Contracts\SenderIdServiceInterface;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;

/**
 * Single Unified SSExpertSystem Gateway Entrypoint.
 *
 * @method static SmsServiceInterface sms()
 * @method static TemplateServiceInterface template()
 * @method static TemplateServiceInterface templates()
 * @method static BalanceServiceInterface balance()
 * @method static SenderIdServiceInterface senderId()
 * @method static SenderIdServiceInterface senderIds()
 * @method static GroupServiceInterface group()
 * @method static GroupServiceInterface groups()
 * @method static SmsApiResponse sendOtp(string $mobile, string $otp, ?string $templateId = null)
 * @method static SmsApiResponse send(SmsData|array $smsData)
 * @method static SmsApiResponse sendBulk(BulkSmsData|array $bulkData, ?string $templateId = null)
 * @method static float getCredits()
 *
 * @see \FeTech\SSExpert\SSExpertManager
 */
class SSExpert extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ssexpert';
    }
}
