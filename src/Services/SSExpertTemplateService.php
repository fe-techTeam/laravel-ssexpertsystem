<?php

namespace FeTech\SSExpert\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\DTOs\TemplateApiResponse;
use FeTech\SSExpert\DTOs\TemplateData;
use FeTech\SSExpert\DTOs\TemplateResponse;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;

class SSExpertTemplateService implements TemplateServiceInterface
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

    /**
     * Create a clone with custom credentials.
     */
    public function withCredentials(string $apiKey, string $clientId): self
    {
        $clone = clone $this;
        $clone->apiKey = $apiKey;
        $clone->clientId = $clientId;

        return $clone;
    }

    /**
     * Create a clone with custom base URL.
     */
    public function withBaseUrl(string $baseUrl): self
    {
        $clone = clone $this;
        $clone->baseUrl = rtrim($baseUrl, '/');

        return $clone;
    }

    /**
     * Get configured API key.
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get configured Client ID.
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Build base HTTP client instance.
     */
    protected function httpClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, throw: false)
            ->acceptJson()
            ->asJson();
    }

    /**
     * Retrieve all registered templates for the account.
     *
     * @return Collection<int, TemplateResponse>
     *
     * @throws SSExpertApiException
     */
    public function list(): Collection
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/Template', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
            ]);

            $data = $this->handleResponse($response, 'list');

            $templates = collect();
            $items = $data['Data'] ?? $data['data'] ?? [];

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $templates->push(TemplateResponse::fromArray($item));
                    }
                }
            }

            return $templates;
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertTemplateService::list: Network or client exception', [
                'error' => $e->getMessage(),
            ]);

            throw new SSExpertApiException('Failed to fetch templates from SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Create a new template.
     *
     * @throws SSExpertApiException
     */
    public function create(TemplateData|array $data): TemplateApiResponse
    {
        $this->validateCredentials();

        $dto = is_array($data) ? TemplateData::fromArray($data) : $data;
        $payload = $dto->toPayload($this->apiKey, $this->clientId);

        try {
            $response = $this->httpClient()->post('/api/v2/Template', $payload);
            $parsed = $this->handleResponse($response, 'create');

            return TemplateApiResponse::fromArray($parsed);
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertTemplateService::create: Network or client exception', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw new SSExpertApiException('Failed to create template on SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Update an existing template by its internal ID.
     *
     * @throws SSExpertApiException
     */
    public function update(int $id, TemplateData|array $data): TemplateApiResponse
    {
        $this->validateCredentials();

        $dto = is_array($data) ? TemplateData::fromArray($data) : $data;
        $payload = $dto->toPayload($this->apiKey, $this->clientId);

        try {
            $response = $this->httpClient()
                ->withQueryParameters(['id' => $id])
                ->put('/api/v2/Template', $payload);

            $parsed = $this->handleResponse($response, 'update');

            return TemplateApiResponse::fromArray($parsed);
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertTemplateService::update: Network or client exception', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw new SSExpertApiException('Failed to update template on SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Delete a template by its internal ID.
     *
     * @throws SSExpertApiException
     */
    public function delete(int $id): TemplateApiResponse
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()
                ->withQueryParameters([
                    'ApiKey' => $this->apiKey,
                    'ClientId' => $this->clientId,
                    'id' => $id,
                ])
                ->delete('/api/v2/Template');

            $parsed = $this->handleResponse($response, 'delete');

            return TemplateApiResponse::fromArray($parsed);
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertTemplateService::delete: Network or client exception', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw new SSExpertApiException('Failed to delete template on SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Find a template by its system internal template ID.
     */
    public function findById(int $id): ?TemplateResponse
    {
        return $this->list()->first(fn (TemplateResponse $t) => $t->templateId === $id);
    }

    /**
     * Find a template by its DLT Template ID.
     */
    public function findByDltTemplateId(string $dltTemplateId): ?TemplateResponse
    {
        return $this->list()->first(fn (TemplateResponse $t) => (string) $t->dltTemplateId === (string) $dltTemplateId);
    }

    /**
     * Find a template by its Template Name.
     */
    public function findByName(string $name): ?TemplateResponse
    {
        $cleanName = strtolower(trim($name));

        return $this->list()->first(fn (TemplateResponse $t) => strtolower(trim($t->templateName)) === $cleanName);
    }

    /**
     * Validate configured credentials.
     *
     * @throws SSExpertAuthException
     */
    protected function validateCredentials(): void
    {
        if (trim($this->apiKey) === '' || trim($this->clientId) === '') {
            throw new SSExpertAuthException('SSExpert API Key and Client ID must be configured.');
        }
    }

    /**
     * Handle HTTP response, checking for status and error codes.
     *
     * @throws SSExpertAuthException
     * @throws SSExpertApiException
     */
    protected function handleResponse(Response $response, string $operation): array
    {
        $status = $response->status();
        $body = $response->json();

        if ($status === 401) {
            Log::error("SSExpertTemplateService::$operation: 401 Unauthorized", [
                'response' => $body,
            ]);

            throw new SSExpertAuthException('Unauthorized: Invalid SSExpert API key or client ID.', 401, 401, is_array($body) ? $body : []);
        }

        if ($status >= 500) {
            Log::error("SSExpertTemplateService::$operation: $status Server Error", [
                'response' => $response->body(),
            ]);

            throw new SSExpertApiException("SSExpert server returned $status error.", $status, $status, is_array($body) ? $body : []);
        }

        if (! $response->successful() || ! is_array($body)) {
            Log::error("SSExpertTemplateService::$operation: Request failed ($status)", [
                'response' => $response->body(),
            ]);

            throw new SSExpertApiException("SSExpert request failed with status $status.", $status, $status, is_array($body) ? $body : []);
        }

        return $body;
    }
}
