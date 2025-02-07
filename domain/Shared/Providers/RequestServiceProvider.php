<?php

namespace Domain\Shared\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Request::macro('includes', function () {
            /** @var \Illuminate\Http\Request */
            $request = $this;

            // We turn a query param that looks like this: ?include=param1,param2
            // to an Collection<['param1', 'param2']>
            return $request->string('include')->isNotEmpty()
                ? $request->string('include')->explode(',')
                : new Collection([]);
        });

        Request::macro('filters', function () {
            /** @var \Illuminate\Http\Request */
            $request = $this;

            // We turn a query param that looks like this: ?filter[param1]=value1&filter[param2]=value2
            // to a Collection<['param1' => 'value1', 'param2' => 'value2']>
            return $request->collect('filter');
        });
    }
}
