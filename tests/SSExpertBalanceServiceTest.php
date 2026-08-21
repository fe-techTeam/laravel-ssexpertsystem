<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\DTOs\BalanceResponse;
use FeTech\SSExpert\Facades\SSExpertBalance;
use FeTech\SSExpert\Services\SSExpertBalanceService;

class SSExpertBalanceServiceTest extends TestCase
{
    protected SSExpertBalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new SSExpertBalanceService($this->getPackageConfig());
    }

    public function test_get_balance_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Balance*' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => [
                    [
                        'PluginType' => 'SMS',
                        'Credits' => 'credit17935.000000',
                    ],
                ],
            ], 200),
        ]);

        $list = $this->balanceService->list();
        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(1, $list);

        $primary = $this->balanceService->get();
        $this->assertInstanceOf(BalanceResponse::class, $primary);
        $this->assertEquals('SMS', $primary->pluginType);
        $this->assertEquals(17935.0, $primary->credits);
        $this->assertEquals('credit17935.000000', $primary->rawCredits);
        $this->assertEquals(17935.0, $this->balanceService->getCredits());
    }

    public function test_balance_facade(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Balance*' => Http::response([
                'ErrorCode' => 0,
                'Data' => [
                    [
                        'PluginType' => 'SMS',
                        'Credits' => '5000',
                    ],
                ],
            ], 200),
        ]);

        $credits = SSExpertBalance::getCredits();
        $this->assertEquals(5000.0, $credits);
    }
}
