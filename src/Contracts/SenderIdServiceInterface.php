<?php

namespace FeTech\SSExpert\Contracts;

use Illuminate\Support\Collection;
use FeTech\SSExpert\DTOs\SenderIdResponse;

interface SenderIdServiceInterface
{
    /**
     * List all Sender IDs (headers) for the account.
     *
     * @return Collection<int, SenderIdResponse>
     */
    public function list(): Collection;

    /**
     * Find a sender ID by its 6-character header name.
     */
    public function findByName(string $senderId): ?SenderIdResponse;

    /**
     * Submit request for a new sender ID.
     */
    public function create(string $senderId, string $purpose, ?int $productId = null): array;

    /**
     * Delete a sender ID by its ID.
     */
    public function delete(int $id): array;
}
