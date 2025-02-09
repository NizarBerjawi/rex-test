<?php

namespace Domain\Contact\Providers;

use Domain\Contact\Repositories\ContactRepository;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ContactRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, fn () => new ContactRepository);
    }
}
