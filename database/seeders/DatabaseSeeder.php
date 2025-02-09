<?php

namespace Database\Seeders;

use Database\Seeders\Contact\ContactSeeder;
use Database\Seeders\Contact\EmailSeeder;
use Database\Seeders\Contact\PhoneSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ContactSeeder::class,
            EmailSeeder::class,
            PhoneSeeder::class,
        ]);
    }
}
