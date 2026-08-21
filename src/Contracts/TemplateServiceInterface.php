<?php

namespace FeTech\SSExpert\Contracts;

use Illuminate\Support\Collection;
use FeTech\SSExpert\DTOs\TemplateApiResponse;
use FeTech\SSExpert\DTOs\TemplateData;
use FeTech\SSExpert\DTOs\TemplateResponse;

interface TemplateServiceInterface
{
    /**
     * Retrieve all registered templates for the account.
     *
     * @return Collection<int, TemplateResponse>
     */
    public function list(): Collection;

    /**
     * Create a new template.
     *
     * @param  TemplateData|array  $data
     * @return TemplateApiResponse
     */
    public function create(TemplateData|array $data): TemplateApiResponse;

    /**
     * Update an existing template by its internal ID.
     *
     * @param  int  $id
     * @param  TemplateData|array  $data
     * @return TemplateApiResponse
     */
    public function update(int $id, TemplateData|array $data): TemplateApiResponse;

    /**
     * Delete a template by its internal ID.
     *
     * @param  int  $id
     * @return TemplateApiResponse
     */
    public function delete(int $id): TemplateApiResponse;

    /**
     * Find a template by its system internal template ID.
     *
     * @param  int  $id
     * @return TemplateResponse|null
     */
    public function findById(int $id): ?TemplateResponse;

    /**
     * Find a template by its DLT Template ID.
     *
     * @param  string  $dltTemplateId
     * @return TemplateResponse|null
     */
    public function findByDltTemplateId(string $dltTemplateId): ?TemplateResponse;

    /**
     * Find a template by its Template Name.
     *
     * @param  string  $name
     * @return TemplateResponse|null
     */
    public function findByName(string $name): ?TemplateResponse;
}
