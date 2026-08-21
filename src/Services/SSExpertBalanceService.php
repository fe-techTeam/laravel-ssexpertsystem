<?php

namespace FeTech\SSExpert\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;
use FeTech\SSExpert\DTOs\BalanceResponse;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;

class SSExpertBalanceService implements BalanceServiceInterface
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
     * Retrieve all balance / credit records for the account.
     *
     * @return Collection<int, BalanceResponse>
     *
     * @throws SSExpertApiException
     */
    public function list(): Collection
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/Balance', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
            ]);

            $body = $this->handleResponse($response, 'balance');
            $items = $body['Data'] ?? $body['data'] ?? [];

            $collection = collect();
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $collection->push(BalanceResponse::fromArray($item));
                    }
                }
            }

            return $collection;
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertBalanceService::list: Exception', ['error' => $e->getMessage()]);

            throw new SSExpertApiException('Failed to fetch balance from SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Get primary balance record.
     */
    public function get(): ?BalanceResponse
    {
        return $this->list()->first();
    }

    /**
     * Get current SMS credits count as a float.
     */
    public function getCredits(): float
    {
        $record = $this->get();

        return $record ? $record->credits : 0.0;
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
            Log::error("SSExpertBalanceService::$operation: 401 Unauthorized", ['response' => $body]);
            throw new SSExpertAuthException('Unauthorized: Invalid SSExpert API key or client ID.', 401, 401, is_array($body) ? $body : []);
        }

        if ($status >= 500) {
            Log::error("SSExpertBalanceService::$operation: $status Server Error", ['response' => $response->body()]);
            throw new SSExpertApiException("SSExpert server returned $status error.", $status, $status, is_array($body) ? $body : []);
        }

        if (! $response->successful() || ! is_array($body)) {
            Log::error("SSExpertBalanceService::$operation: Request failed ($status)", ['response' => $response->body()]);
            throw new SSExpertApiException("SSExpert request failed with status $status.", $status, $status, is_array($body) ? $body : []);
        }

        return $body;
    }
}
