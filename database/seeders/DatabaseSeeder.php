<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Jan's account (pre-hashed password)
        User::factory()->create([
            'name' => 'Jan',
            'email' => 'jan.tuitman@gmail.com',
            'password' => '$2y$12$jlv/ygn5GFUFpbj7vPzolOnWId1FbXurBAAQ4Sv5Q5nse4VAeGTBC',
        ]);
    }
}
