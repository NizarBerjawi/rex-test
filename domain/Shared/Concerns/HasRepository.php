<?php

namespace Domain\Shared\Concerns;

use Illuminate\Support\Str;

/**
 * @template TRepository of \Illuminate\Database\Eloquent\Factories\Factory
 */
trait HasRepository
{
    /**
     * Create a new repository instance for the model.
     *
     * @return TRepository|null
     */
    public static function repository()
    {
        $parts = Str::of(get_called_class())->explode('\\');
        $domain = $parts[1];
        $model = $parts->last();

        return app("Domain\\{$domain}\\Repositories\\{$model}Repository");
    }
}
