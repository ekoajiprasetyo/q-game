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
        // Seed users, topics and questions
        $this->call([
            UserSeeder::class,
            TopicSeeder::class,
            QuestionSeeder::class,
        ]);
    }
}
