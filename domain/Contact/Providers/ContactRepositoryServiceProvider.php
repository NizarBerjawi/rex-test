<?php

namespace Domain\Contact\Providers;

use Domain\Contact\Models\Contact;
use Domain\Contact\Repositories\ContactRepository;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class ContactRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, function (Application $app) {
            return new ContactRepository(new Contact);
        });
    }
}
