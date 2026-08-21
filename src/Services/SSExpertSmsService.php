<?php

namespace FeTech\SSExpert\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;

class SSExpertSmsService implements SmsServiceInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected ?string $defaultSenderId;
    protected ?string $defaultPeid;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('ssexpert', []);

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'http://api.ssexpertsystem.com'), '/');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->clientId = (string) ($config['client_id'] ?? '');
        $this->defaultSenderId = ! empty($config['sender_id']) ? (string) $config['sender_id'] : null;
        $this->defaultPeid = ! empty($config['principle_entity_id']) ? (string) $config['principle_entity_id'] : null;
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
     * Send a single SMS message.
     *
     * @throws SSExpertApiException
     */
    public function send(SmsData|array $smsData): SmsApiResponse
    {
        $this->validateCredentials();

        $dto = is_array($smsData) ? SmsData::fromArray($smsData) : $smsData;
        $payload = $dto->toPayload(
            $this->apiKey,
            $this->clientId,
            $this->defaultSenderId,
            $this->defaultPeid
        );

        try {
            $response = $this->httpClient()->post('/api/v2/SendSMS', $payload);
            $parsed = $this->handleResponse($response, 'send');

            $apiResponse = SmsApiResponse::fromArray($parsed);

            if ($apiResponse->isSuccess()) {
                Log::info('SSExpertSmsService::send: SMS sent successfully', [
                    'mobile' => $dto->mobileNumbers,
                    'template_id' => $dto->templateId,
                    'message_id' => $apiResponse->getMessageId(),
                ]);
            } else {
                Log::warning('SSExpertSmsService::send: Gateway returned error', [
                    'mobile' => $dto->mobileNumbers,
                    'template_id' => $dto->templateId,
                    'error' => $apiResponse->getErrorMessage(),
                ]);
            }

            return $apiResponse;
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::send: Exception occurred', [
                'mobile' => $dto->mobileNumbers,
                'template_id' => $dto->templateId,
                'error' => $e->getMessage(),
            ]);

            throw new SSExpertApiException('Failed to send SMS via SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Helper to send an OTP SMS with an approved template.
     */
    public function sendOtp(string $mobile, string $otp, ?string $templateId = null): SmsApiResponse
    {
        $message = "Your Login OTP is {$otp}. Do not share with anyone.";

        return $this->send(new SmsData(
            mobileNumbers: $mobile,
            message: $message,
            templateId: $templateId,
        ));
    }

    /**
     * Send bulk personalized SMS messages in a single API call.
     *
     * @throws SSExpertApiException
     */
    public function sendBulk(BulkSmsData|array $bulkData): SmsApiResponse
    {
        $this->validateCredentials();

        $dto = is_array($bulkData) ? BulkSmsData::fromArray($bulkData) : $bulkData;
        $payload = $dto->toPayload(
            $this->apiKey,
            $this->clientId,
            $this->defaultSenderId,
            $this->defaultPeid
        );

        try {
            $response = $this->httpClient()->post('/api/v2/SendBulkSMS', $payload);
            $parsed = $this->handleResponse($response, 'sendBulk');

            return SmsApiResponse::fromArray($parsed);
        } catch (SSExpertApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::sendBulk: Exception occurred', [
                'count' => count($dto->messageParameters),
                'error' => $e->getMessage(),
            ]);

            throw new SSExpertApiException('Failed to send bulk SMS via SSExpert: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Query delivery status for a specific message by its Gateway Message ID.
     */
    public function getMessageStatus(string $messageId): array
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/MessageStatus', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
                'MessageId' => $messageId,
            ]);

            return $this->handleResponse($response, 'getMessageStatus');
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::getMessageStatus: Exception', ['message_id' => $messageId, 'error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to query message status: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Query SMS delivery status logs for recent days.
     */
    public function getDeliveryReport(int $days = 7): array
    {
        $this->validateCredentials();

        try {
            $response = $this->httpClient()->get('/api/v2/SMS/Status', [
                'ApiKey' => $this->apiKey,
                'ClientId' => $this->clientId,
                'days' => $days,
            ]);

            return $this->handleResponse($response, 'getDeliveryReport');
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::getDeliveryReport: Exception', ['days' => $days, 'error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to query delivery report: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Retrieve detailed SMS transmission logs with pagination and date filter.
     */
    public function getSmsLogs(int $start = 0, int $length = 50, ?string $fromDate = null, ?string $endDate = null): array
    {
        $this->validateCredentials();

        $params = [
            'ApiKey' => $this->apiKey,
            'ClientId' => $this->clientId,
            'start' => $start,
            'length' => $length,
        ];

        if ($fromDate) {
            $params['fromdate'] = $fromDate;
        }

        if ($endDate) {
            $params['enddate'] = $endDate;
        }

        try {
            $response = $this->httpClient()->get('/api/v2/GetSMS', $params);

            return $this->handleResponse($response, 'getSmsLogs');
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::getSmsLogs: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to query SMS logs: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Retrieve summary statistics report by date range.
     */
    public function getReportSummary(?string $fromDate = null, ?string $endDate = null): array
    {
        $this->validateCredentials();

        $params = [
            'ApiKey' => $this->apiKey,
            'ClientId' => $this->clientId,
        ];

        if ($fromDate) {
            $params['fromdate'] = $fromDate;
        }

        if ($endDate) {
            $params['enddate'] = $endDate;
        }

        try {
            $response = $this->httpClient()->get('/api/v2/ReportSummary', $params);

            return $this->handleResponse($response, 'getReportSummary');
        } catch (\Throwable $e) {
            Log::error('SSExpertSmsService::getReportSummary: Exception', ['error' => $e->getMessage()]);
            throw new SSExpertApiException('Failed to query report summary: ' . $e->getMessage(), 0, null, null, $e);
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
            Log::error("SSExpertSmsService::$operation: 401 Unauthorized", ['response' => $body]);
            throw new SSExpertAuthException('Unauthorized: Invalid SSExpert API key or client ID.', 401, 401, is_array($body) ? $body : []);
        }

        if ($status >= 500) {
            Log::error("SSExpertSmsService::$operation: $status Server Error", ['response' => $response->body()]);
            throw new SSExpertApiException("SSExpert server returned $status error.", $status, $status, is_array($body) ? $body : []);
        }

        if (! $response->successful() || ! is_array($body)) {
            Log::error("SSExpertSmsService::$operation: Request failed ($status)", ['response' => $response->body()]);
            throw new SSExpertApiException("SSExpert request failed with status $status.", $status, $status, is_array($body) ? $body : []);
        }

        return $body;
    }
}
