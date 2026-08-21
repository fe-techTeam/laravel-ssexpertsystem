<?php

namespace FeTech\SSExpert\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FeTech\SSExpert\Contracts\GroupServiceInterface;
use FeTech\SSExpert\DTOs\GroupResponse;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;

class SSExpertGroupService implements GroupServiceInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('ssexpert', []);

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'http://api.ssexpertsystem.com'), '/');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->clientId = (string) ($config['client_id'] ?? '');
        $this->timeout = (int) ($config['timeout'] ?? 15);
        $this->retryTimes = (int) ($config['retry']['times'] ?? 3);
        $this->retrySleep = (int) ($config['retry']['sleep'] ?? 100);
    }

    protected function httpClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, throw: false)
            ->acceptJson()
            ->asJson();
    }

    /**
     * List all contact groups.
     *
     * @return Collection<int, GroupResponse>
     */
    public function list(): Collection
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/Group', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
            ]);

            $body = $this->handleResponse($response, 'listGroups');
            $items = $body['Data'] ?? $body['data'] ?? [];

            $collection = collect();
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $collection->push(GroupResponse::fromArray($item));
                    }
                }
            }

            return $collection;
        } catch (\Throwable $e) {
            Log::error('SSExpertGroupService::list: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to list groups: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Create a new contact group.
     */
    public function create(string $groupName): array
    {
        $this->validateCredentials();

        $payload = [
            'groupName' => $groupName,
            'apiKey' => $this->apiKey,
            'clientId' => $this->clientId,
        ];

        try {
            $response = $this->httpClient()->post('/api/v2/Group', $payload);

            return $this->handleResponse($response, 'createGroup');
        } catch (\Throwable $e) {
            Log::error('SSExpertGroupService::create: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to create group: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Update an existing contact group.
     */
    public function update(int $id, string $groupName): array
    {
        $this->validateCredentials();

        $payload = [
            'groupName' => $groupName,
            'apiKey' => $this->apiKey,
            'clientId' => $this->clientId,
        ];

        try {
            $response = $this->httpClient()
                ->withQueryParameters(['id' => $id])
                ->put('/api/v2/Group', $payload);

            return $this->handleResponse($response, 'updateGroup');
        } catch (\Throwable $e) {
            Log::error('SSExpertGroupService::update: Exception', ['id' => $id, 'error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to update group: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Delete a contact group by ID.
     */
    public function delete(int $id): array
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->withQueryParameters([
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
                'id' => $id,
            ])->delete('/api/v2/Group');

            return $this->handleResponse($response, 'deleteGroup');
        } catch (\Throwable $e) {
            Log::error('SSExpertGroupService::delete: Exception', ['id' => $id, 'error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to delete group: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    protected function validateCredentials(): void
    {
        if (trim($this->apiKey) === '' || trim($this->clientId) === '') {
            throw new SSExpertAuthException('SSExpert API Key and Client ID must be configured.');
        }
    }

    protected function handleResponse(Response $response, string $operation): array
    {
        $status = $response->status();
        $body = $response->json();

        if ($status === 401) {
            throw new SSExpertAuthException('Unauthorized: Invalid SSExpert API key or client ID.', 401, 401, is_array($body) ? $body : []);
        }

        if ($status >= 500) {
            throw new SSExpertApiException("SSExpert server returned $status error.", $status, $status, is_array($body) ? $body : []);
        }

        if (! $response->successful() || ! is_array($body)) {
            throw new SSExpertApiException("SSExpert request failed with status $status.", $status, $status, is_array($body) ? $body : []);
        }

        return $body;
    }
}
