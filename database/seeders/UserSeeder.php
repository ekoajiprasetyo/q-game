<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Admin
        User::updateOrCreate(
            ['email' => 'admin@qgame.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Default Teacher
        User::updateOrCreate(
            ['email' => 'guru@qgame.com'],
            [
                'name' => 'Guru',
                'password' => Hash::make('guru123'),
                'role' => 'teacher',
            ]
        );
    }
}
