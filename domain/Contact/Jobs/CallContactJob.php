<?php

namespace App\Jobs;

use Domain\Contact\Exceptions\ContactCallException;
use Domain\Contact\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CallContactJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Contact $contact)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // https://www.twilio.com/docs/voice/make-calls#initiate-an-outbound-call-with-twilio
        $response = Http::post('/fake-twillio-api/scheduleCall', [
            'phone' => $this->contact->phones()->first(),
            'instructions' => 'fake_id',
        ]);

        if ($response->failed()) {
            $e = $response->toException();

            throw new ContactCallException($e->getMessage(), $e);
        }
    }
}
