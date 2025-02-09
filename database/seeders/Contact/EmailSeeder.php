<?php

namespace Database\Seeders\Contact;

use Database\Factories\Contact\EmailFactory;
use Domain\Contact\Models\Contact;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $contacts = Contact::get();

        $contacts->each(function (Contact $contact) {
            (new EmailFactory)
                ->count(fake()->numberBetween(0, 3))
                ->for($contact)
                ->create();
        });
    }
}
