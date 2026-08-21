<?php

namespace FeTech\SSExpert\Contracts;

use Illuminate\Support\Collection;
use FeTech\SSExpert\DTOs\BalanceResponse;

interface BalanceServiceInterface
{
    /**
     * Retrieve all balance / credit records for the account.
     *
     * @return Collection<int, BalanceResponse>
     */
    public function list(): Collection;

    /**
     * Get primary balance record.
     */
    public function get(): ?BalanceResponse;

    /**
     * Get current SMS credits count as a float.
     */
    public function getCredits(): float;
}
