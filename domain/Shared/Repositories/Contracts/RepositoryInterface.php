<?php

namespace Domain\Shared\Repositories\Contracts;

use Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;

interface RepositoryInterface
{
    /**
     * Retrieve all data of repository
     */
    public function all(): Collection;

    /**
     * Retrieve all data of repository, paginated
     */
    public function paginate(?int $limit = null): AbstractPaginator;

    /**
     * Find data by uuid
     */
    public function find(string $uuid): BaseModel;

    /**
     * Save a new entity in repository
     */
    public function create(array $attributes): BaseModel;

    /**
     * Update an entity in the repository by uuid
     */
    public function update(array $attributes, string $uuid): BaseModel;

    /**
     * Upsert an entity in repository by uuid
     */
    public function upsert(array $attributes, string $uuid): BaseModel;

    /**
     * Delete an entity in repository by uuid
     */
    public function delete(string $uuid): bool;

    /**
     * Load a model's relations
     */
    public function with(array $relations);
}
