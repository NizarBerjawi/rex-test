<?php

namespace Database\Seeders\Contact;

use Database\Factories\Contact\ContactFactory;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        (new ContactFactory)->count(100)->create();
    }
}
