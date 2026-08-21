<?php

namespace FeTech\SSExpert\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\DTOs\BalanceResponse;

/**
 * @method static Collection<int, BalanceResponse> list()
 * @method static BalanceResponse|null get()
 * @method static float getCredits()
 *
 * @see \FeTech\SSExpert\Services\SSExpertBalanceService
 */
class SSExpertBalance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BalanceServiceInterface::class;
    }
}
