<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\DTOs\GroupResponse;
use FeTech\SSExpert\Facades\SSExpertGroup;
use FeTech\SSExpert\Services\SSExpertGroupService;

class SSExpertGroupServiceTest extends TestCase
{
    protected SSExpertGroupService $groupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupService = new SSExpertGroupService($this->getPackageConfig());
    }

    public function test_list_and_manage_groups(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Group*' => Http::response([
                'ErrorCode' => 0,
                'Data' => [
                    [
                        'GroupId' => 10,
                        'GroupName' => 'Technicians',
                        'ContactCount' => 125,
                    ],
                ],
            ], 200),
            'http://api.ssexpertsystem.com/api/v2/Group' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'Success',
            ], 200),
        ]);

        $list = $this->groupService->list();
        $this->assertInstanceOf(Collection::class, $list);
        $first = $list->first();
        $this->assertInstanceOf(GroupResponse::class, $first);
        $this->assertEquals('Technicians', $first->groupName);
        $this->assertEquals(125, $first->contactCount);

        $created = $this->groupService->create('Distributors');
        $this->assertEquals(0, $created['ErrorCode']);

        $updated = $this->groupService->update(10, 'Technicians Tier 1');
        $this->assertEquals(0, $updated['ErrorCode']);

        $deleted = $this->groupService->delete(10);
        $this->assertEquals(0, $deleted['ErrorCode']);
    }
}
