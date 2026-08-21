<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\DTOs\SenderIdResponse;
use FeTech\SSExpert\Services\SSExpertSenderIdService;

class SSExpertSenderIdServiceTest extends TestCase
{
    protected SSExpertSenderIdService $senderIdService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->senderIdService = new SSExpertSenderIdService($this->getPackageConfig());
    }

    public function test_list_sender_ids_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SenderId*' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => [
                    [
                        'Id' => 292,
                        'SenderId' => 'TESTID',
                        'CompanyId' => 1001,
                        'IsActive' => true,
                        'IsApproved' => 1,
                        'Purpose' => 'sms',
                    ],
                ],
            ], 200),
        ]);

        $list = $this->senderIdService->list();
        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(1, $list);

        $first = $list->first();
        $this->assertInstanceOf(SenderIdResponse::class, $first);
        $this->assertEquals('TESTID', $first->senderId);
        $this->assertTrue($first->isApproved);
        $this->assertTrue($first->isActive);

        $found = $this->senderIdService->findByName('testid');
        $this->assertNotNull($found);
        $this->assertEquals(292, $found->id);
    }

    public function test_create_and_delete_sender_id(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SenderId' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'Created',
            ], 200),
            'http://api.ssexpertsystem.com/api/v2/SenderId*' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'Deleted',
            ], 200),
        ]);

        $res = $this->senderIdService->create('TESTID', 'Service Alerts');
        $this->assertEquals(0, $res['ErrorCode']);

        $del = $this->senderIdService->delete(292);
        $this->assertEquals(0, $del['ErrorCode']);
    }
}
