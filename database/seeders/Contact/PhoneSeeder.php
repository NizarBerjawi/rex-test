<?php

namespace Database\Seeders\Contact;

use Database\Factories\Contact\PhoneFactory;
use Domain\Contact\Models\Contact;
use Illuminate\Database\Seeder;

class PhoneSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $contacts = Contact::get();

        $contacts->each(function (Contact $contact) {
            (new PhoneFactory)
                ->count(fake()->numberBetween(0, 3))
                ->for($contact)
                ->create();
        });
    }
}
