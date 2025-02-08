<?php

namespace Domain\Shared\Repositories;

use Domain\Shared\Models\BaseModel;
use Domain\Shared\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;

class BaseRepository implements RepositoryInterface
{
    public function __construct(public BaseModel $model, public array $relations = []) {}

    /**
     * The relations to loaded
     */
    public function with(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Return all instances of the model
     */
    public function all(): Collection
    {
        return $this->model->with($this->relations)->all();
    }

    /**
     * Return a paginated collection of the model
     */
    public function paginate(?int $limit = null): AbstractPaginator
    {
        /** @var \Illuminate\Pagination\AbstractPaginator */
        $paginator = $this->model
            ->with($this->relations)
            ->paginate($limit);

        return $paginator->withQueryString();
    }

    /**
     * Attempt to find a model by uuid
     */
    public function find(string $uuid): BaseModel
    {
        return $this->model->with($this->relations)->findOrFail($uuid);
    }

    /**
     * Create a new model with the provided attributes
     */
    public function create(array $attributes): BaseModel
    {
        return $this->model->with($this->relations)->create($attributes);
    }

    /**
     * Attempt to update a model with the provided attributes.
     */
    public function update(array $attributes, string $uuid): BaseModel
    {
        $model = $this->model->findOrFail($uuid);

        $model->fill($attributes);

        $model->save();

        return $model->load($this->relations);
    }

    /**
     * Attempt to update a model if it is found. Otherwise, create it
     * with the provided attributes
     */
    public function upsert(array $attributes, string $uuid): BaseModel
    {
        $this->model->unguard();

        $model = $this->model->updateOrCreate(
            [$this->model->getKeyName() => $uuid], $attributes
        );

        $this->model->reguard();

        return $model->load($this->relations);
    }

    /**
     * Delete a specified model by uuid
     */
    public function delete(string $uuid): bool
    {
        return $this->model->where($this->model->getKeyName(), $uuid)->delete();
    }
}
