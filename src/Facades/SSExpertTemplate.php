<?php

namespace FeTech\SSExpert\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use FeTech\SSExpert\Contracts\TemplateServiceInterface;
use FeTech\SSExpert\DTOs\TemplateApiResponse;
use FeTech\SSExpert\DTOs\TemplateData;
use FeTech\SSExpert\DTOs\TemplateResponse;
use FeTech\SSExpert\Services\SSExpertTemplateService;

/**
 * @method static Collection<int, TemplateResponse> list()
 * @method static TemplateApiResponse create(TemplateData|array $data)
 * @method static TemplateApiResponse update(int $id, TemplateData|array $data)
 * @method static TemplateApiResponse delete(int $id)
 * @method static TemplateResponse|null findById(int $id)
 * @method static TemplateResponse|null findByDltTemplateId(string $dltTemplateId)
 * @method static TemplateResponse|null findByName(string $name)
 * @method static SSExpertTemplateService withCredentials(string $apiKey, string $clientId)
 * @method static SSExpertTemplateService withBaseUrl(string $baseUrl)
 *
 * @see \FeTech\SSExpert\Services\SSExpertTemplateService
 */
class SSExpertTemplate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TemplateServiceInterface::class;
    }
}
