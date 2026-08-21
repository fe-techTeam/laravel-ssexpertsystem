<?php

namespace FeTech\SSExpert\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\DTOs\GroupResponse;

/**
 * @method static Collection<int, GroupResponse> list()
 * @method static array create(string $groupName)
 * @method static array update(int $id, string $groupName)
 * @method static array delete(int $id)
 *
 * @see \FeTech\SSExpert\Services\SSExpertGroupService
 */
class SSExpertGroup extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GroupServiceInterface::class;
    }
}
