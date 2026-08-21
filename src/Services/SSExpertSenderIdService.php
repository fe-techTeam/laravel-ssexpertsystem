<?php

namespace FeTech\SSExpert\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FeTech\SSExpert\Contracts\SenderIdServiceInterface;
use FeTech\SSExpert\DTOs\SenderIdResponse;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;

class SSExpertSenderIdService implements SenderIdServiceInterface
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
     * List all Sender IDs.
     *
     * @return Collection<int, SenderIdResponse>
     */
    public function list(): Collection
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/SenderId', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
            ]);

            $body = $this->handleResponse($response, 'listSenderIds');
            $items = $body['Data'] ?? $body['data'] ?? [];

            $collection = collect();
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $collection->push(SenderIdResponse::fromArray($item));
                    }
                }
            }

            return $collection;
        } catch (\Throwable $e) {
            Log::error('SSExpertSenderIdService::list: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to list Sender IDs: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Find a sender ID by its 6-character header name.
     */
    public function findByName(string $senderId): ?SenderIdResponse
    {
        $clean = strtoupper(trim($senderId));

        return $this->list()->first(fn (SenderIdResponse $s) => strtoupper(trim($s->senderId)) === $clean);
    }

    /**
     * Submit request for a new sender ID.
     */
    public function create(string $senderId, string $purpose, ?int $productId = null): array
    {
        $this->validateCredentials();

        $payload = [
            'senderId' => $senderId,
            'purpose' => $purpose,
            'productId' => $productId ?: 1,
            'apiKey' => $this->apiKey,
            'clientId' => $this->clientId,
        ];

        try {
            $response = $this->httpClient()->post('/api/v2/SenderId', $payload);

            return $this->handleResponse($response, 'createSenderId');
        } catch (\Throwable $e) {
            Log::error('SSExpertSenderIdService::create: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to create Sender ID: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Delete a sender ID by its ID.
     */
    public function delete(int $id): array
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->withQueryParameters([
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
                'id' => $id,
            ])->delete('/api/v2/SenderId');

            return $this->handleResponse($response, 'deleteSenderId');
        } catch (\Throwable $e) {
            Log::error('SSExpertSenderIdService::delete: Exception', ['id' => $id, 'error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to delete Sender ID: ' . $e->getMessage(), 0, null, null, $e);
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
