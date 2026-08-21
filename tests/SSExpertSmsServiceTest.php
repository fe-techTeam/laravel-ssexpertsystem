<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\DTOs\BulkMessageItem;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\SmsApiResponse;
use FeTech\SSExpert\DTOs\SmsData;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Services\SSExpertSmsService;

class SSExpertSmsServiceTest extends TestCase
{
    protected SSExpertSmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = new SSExpertSmsService($this->getPackageConfig());
    }

    public function test_send_single_sms_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => [
                    [
                        'MessageErrorCode' => 0,
                        'MessageErrorDescription' => 'Success',
                        'MobileNumber' => '919876543210',
                        'MessageId' => 'mock-uuid-112233',
                    ],
                ],
            ], 200),
        ]);

        $sms = new SmsData(
            mobileNumbers: '9876543210',
            message: 'Your Login OTP is 456789. Do not share with anyone.',
            templateId: '1107160000000000001',
        );

        $response = $this->smsService->send($sms);

        $this->assertInstanceOf(SmsApiResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('mock-uuid-112233', $response->getMessageId());

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'http://api.ssexpertsystem.com/api/v2/SendSMS'
                && $body['mobileNumbers'] === '9876543210'
                && $body['senderId'] === 'TESTID'
                && $body['templateId'] === '1107160000000000001'
                && $body['apiKey'] === 'test_api_key'
                && $body['clientId'] === 'test_client_id';
        });
    }

    public function test_send_otp_helper(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response([
                'ErrorCode' => 0,
                'Data' => [
                    [
                        'MessageErrorCode' => 0,
                        'MessageId' => 'otp-message-id-999',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->smsService->sendOtp('9876543210', '839201', '1107160000000000001');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('otp-message-id-999', $response->getMessageId());

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $body['mobileNumbers'] === '9876543210'
                && str_contains($body['message'], '839201')
                && $body['templateId'] === '1107160000000000001';
        });
    }

    public function test_send_bulk_sms(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendBulkSMS' => Http::response([
                'ErrorCode' => 0,
                'Data' => 'Bulk Accepted',
            ], 200),
        ]);

        $bulk = new BulkSmsData(
            messages: [
                new BulkMessageItem('9876543210', 'Hi User 1'),
                new BulkMessageItem('9123456780', 'Hi User 2'),
            ],
            templateId: '1107160000000000001',
        );

        $response = $this->smsService->sendBulk($bulk);

        $this->assertTrue($response->isSuccess());
    }

    public function test_empty_message_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SmsData('9876543210', '');
    }

    public function test_gateway_error_response_handling(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response([
                'ErrorCode' => 104,
                'ErrorDescription' => 'Invalid Template ID',
                'Data' => null,
            ], 200),
        ]);

        $response = $this->smsService->sendOtp('9876543210', '123456', '1107160000000000001');

        $this->assertFalse($response->isSuccess());
        $this->assertEquals(104, $response->errorCode);
        $this->assertEquals('Invalid Template ID', $response->getErrorMessage());
    }

    public function test_server_failure_throws_exception(): void
    {
        $this->expectException(SSExpertApiException::class);

        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response('Gateway Timeout', 504),
        ]);

        $this->smsService->sendOtp('9876543210', '123456');
    }
}
