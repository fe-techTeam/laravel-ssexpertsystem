<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\Contracts\SenderIdServiceInterface;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\Facades\SSExpert;

class SSExpertManagerTest extends TestCase
{
    public function test_umbrella_facade_sub_services(): void
    {
        $this->assertInstanceOf(SmsServiceInterface::class, SSExpert::sms());
        $this->assertInstanceOf(TemplateServiceInterface::class, SSExpert::template());
        $this->assertInstanceOf(TemplateServiceInterface::class, SSExpert::templates());
        $this->assertInstanceOf(BalanceServiceInterface::class, SSExpert::balance());
        $this->assertInstanceOf(SenderIdServiceInterface::class, SSExpert::senderId());
        $this->assertInstanceOf(SenderIdServiceInterface::class, SSExpert::senderIds());
        $this->assertInstanceOf(GroupServiceInterface::class, SSExpert::group());
        $this->assertInstanceOf(GroupServiceInterface::class, SSExpert::groups());
    }

    public function test_umbrella_facade_shortcuts(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'msg_123',
            ], 200),
            'http://api.ssexpertsystem.com/api/v2/SendBulkSMS' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'bulk_accepted',
            ], 200),
            'http://api.ssexpertsystem.com/api/v2/Balance*' => Http::response([
                'ErrorCode' => 0,
                'Data' => [
                    ['PluginType' => 'SMS', 'Credits' => '1500'],
                ],
            ], 200),
        ]);

        $res = SSExpert::sendOtp('9876543210', '999111');
        $this->assertTrue($res->isSuccess());

        // Test bulk shortcut with pure key-value array
        $bulkRes = SSExpert::sendBulk([
            '9876543210' => 'Hello User 1',
            '9123456780' => 'Hello User 2',
        ], '1107160000000000001');
        $this->assertTrue($bulkRes->isSuccess());

        $credits = SSExpert::getCredits();
        $this->assertEquals(1500.0, $credits);
    }
}
