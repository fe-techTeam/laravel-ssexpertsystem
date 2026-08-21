<?php

namespace FeTech\SSExpert\Contracts;

use Illuminate\Support\Collection;
use FeTech\SSExpert\DTOs\GroupResponse;

interface GroupServiceInterface
{
    /**
     * List all contact groups.
     *
     * @return Collection<int, GroupResponse>
     */
    public function list(): Collection;

    /**
     * Create a new contact group.
     */
    public function create(string $groupName): array;

    /**
     * Update an existing contact group name.
     */
    public function update(int $id, string $groupName): array;

    /**
     * Delete a contact group by ID.
     */
    public function delete(int $id): array;
}
