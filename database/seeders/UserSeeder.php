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
        // Standard Operator
        User::create([
            'username' => 'operator',
            'password' => Hash::make('password'),
            'energy_points' => 100,
            'level' => 1,
            'cpu' => 800,
            'ram' => 512,
            'ssd' => 100,
            'role' => 'player',
            'stats' => [
                'xp' => 0,
                'credits' => 150,
            ],
            'last_energy_update' => now(),
        ]);

        // Second Operator (for testing)
        User::create([
            'username' => 'operator2',
            'password' => Hash::make('password'),
            'energy_points' => 100,
            'level' => 2,
            'cpu' => 1000,
            'ram' => 1024,
            'ssd' => 95,
            'role' => 'player',
            'stats' => [
                'xp' => 1200,
                'credits' => 300,
            ],
            'last_energy_update' => now(),
        ]);

        // Administrator
        User::create([
            'username' => 'wheelkorner@gmail.com',
            'password' => Hash::make('354354**'),
            'energy_points' => 1000,
            'level' => 99,
            'cpu' => 5000,
            'ram' => 16384,
            'ssd' => 100,
            'role' => 'admin',
            'stats' => [
                'xp' => 99999,
                'credits' => 999999,
            ],
            'last_energy_update' => now(),
        ]);
    }
}
