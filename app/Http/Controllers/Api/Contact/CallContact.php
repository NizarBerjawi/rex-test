<?php

namespace App\Http\Controllers\Api\Contact;

use App\Jobs\CallContactJob;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;

class CallContact
{
    /**
     * Call a Contact
     */
    public function __invoke(string $contactUuid)
    {
        $contacts = app(ContactRepositoryInterface::class);

        CallContactJob::dispatch($contacts->find($contactUuid));
    }
}
