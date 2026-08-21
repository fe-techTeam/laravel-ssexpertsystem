<?php

namespace FeTech\SSExpert\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\DTOs\TemplateData;
use FeTech\SSExpert\DTOs\TemplateResponse;
use FeTech\SSExpert\Exceptions\SSExpertApiException;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;
use FeTech\SSExpert\Services\SSExpertTemplateService;

class SSExpertTemplateServiceTest extends TestCase
{
    protected SSExpertTemplateService $templateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateService = new SSExpertTemplateService($this->getPackageConfig());
    }

    public function test_list_templates_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template*' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => [
                    [
                        'TemplateId' => 101,
                        'TemplateName' => 'OTP_SECURITY',
                        'MessageTemplate' => 'Your OTP is {#var#}. SampleCompany',
                        'DltTemplateId' => '1107160000000000001',
                        'IsApproved' => 1,
                    ],
                    [
                        'templateId' => 102,
                        'templateName' => 'SERVICE_ALERT',
                        'messageTemplate' => 'Service update: {#var#}. SampleCompany',
                        'dltTemplateId' => '1107160000000000002',
                        'isApproved' => 0,
                    ],
                ],
            ], 200),
        ]);

        $templates = $this->templateService->list();

        $this->assertInstanceOf(Collection::class, $templates);
        $this->assertCount(2, $templates);

        $first = $templates->first();
        $this->assertInstanceOf(TemplateResponse::class, $first);
        $this->assertEquals(101, $first->templateId);
        $this->assertEquals('OTP_SECURITY', $first->templateName);
        $this->assertEquals('Your OTP is {#var#}. SampleCompany', $first->messageTemplate);
        $this->assertEquals('1107160000000000001', $first->dltTemplateId);
        $this->assertTrue($first->isApproved);

        $second = $templates->last();
        $this->assertEquals(102, $second->templateId);
        $this->assertFalse($second->isApproved);
    }

    public function test_find_by_dlt_template_id(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template*' => Http::response([
                'ErrorCode' => 0,
                'Data' => [
                    [
                        'TemplateId' => 101,
                        'TemplateName' => 'OTP_SECURITY',
                        'MessageTemplate' => 'Your OTP is {#var#}. SampleCompany',
                        'DltTemplateId' => '1107160000000000001',
                        'IsApproved' => 1,
                    ],
                ],
            ], 200),
        ]);

        $template = $this->templateService->findByDltTemplateId('1107160000000000001');

        $this->assertNotNull($template);
        $this->assertEquals('OTP_SECURITY', $template->templateName);

        $notFound = $this->templateService->findByDltTemplateId('9999999999999999999');
        $this->assertNull($notFound);
    }

    public function test_create_template_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Template created successfully',
                'Data' => 103,
            ], 200),
        ]);

        $dto = new TemplateData(
            templateName: 'PAYMENT_ALERT',
            messageTemplate: 'Payment received: {#var#}. SampleCompany',
            dltTemplateId: '1107160000000000003'
        );

        $response = $this->templateService->create($dto);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(0, $response->errorCode);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'http://api.ssexpertsystem.com/api/v2/Template'
                && $data['templateName'] === 'PAYMENT_ALERT'
                && $data['templateId'] === '1107160000000000003'
                && $data['apiKey'] === 'test_api_key'
                && $data['clientId'] === 'test_client_id';
        });
    }

    public function test_update_template_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template*' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Template updated successfully',
            ], 200),
        ]);

        $dto = new TemplateData(
            templateName: 'PAYMENT_ALERT_V2',
            messageTemplate: 'Payment of {#var#} received. SampleCompany',
            dltTemplateId: '1107160000000000003'
        );

        $response = $this->templateService->update(103, $dto);

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'id=103')
                && $request['templateName'] === 'PAYMENT_ALERT_V2';
        });
    }

    public function test_delete_template_success(): void
    {
        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template*' => Http::response([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Template deleted successfully',
            ], 200),
        ]);

        $response = $this->templateService->delete(103);

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'id=103')
                && str_contains($request->url(), 'ApiKey=test_api_key')
                && $request->method() === 'DELETE';
        });
    }

    public function test_missing_credentials_throws_auth_exception(): void
    {
        $this->expectException(SSExpertAuthException::class);

        $service = new SSExpertTemplateService(['api_key' => '', 'client_id' => '']);
        $service->list();
    }

    public function test_server_error_throws_api_exception(): void
    {
        $this->expectException(SSExpertApiException::class);

        Http::fake([
            'http://api.ssexpertsystem.com/api/v2/Template*' => Http::response('Server Error', 500),
        ]);

        $this->templateService->list();
    }
}
