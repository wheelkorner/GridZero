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
            'reputation_score' => 150,
            'cpu' => 800,
            'ram' => 512,
            'ssd' => 100,
            'role' => 'player',
            'is_npc' => false,
            'stats' => [
                'xp' => 0,
                'credits' => 150,
            ],
            'last_energy_update' => now(),
            'last_seen_ip' => '127.0.0.1',
        ]);

        // Second Operator (for testing)
        User::create([
            'username' => 'operator2',
            'password' => Hash::make('password'),
            'energy_points' => 100,
            'level' => 2,
            'reputation_score' => 450,
            'cpu' => 1000,
            'ram' => 1024,
            'ssd' => 95,
            'role' => 'player',
            'is_npc' => false,
            'stats' => [
                'xp' => 1200,
                'credits' => 300,
            ],
            'last_energy_update' => now(),
            'last_seen_ip' => '192.168.1.15',
        ]);

        // Administrator (NOT a player)
        User::create([
            'username' => 'wheelkorner@gmail.com',
            'password' => Hash::make('354354**'),
            'energy_points' => 1000,
            'level' => 999,
            'reputation_score' => 999999,
            'cpu' => 10000,
            'ram' => 32768,
            'ssd' => 100,
            'role' => 'admin',
            'is_npc' => false,
            'stats' => [], // Empty stats for admin
            'last_energy_update' => now(),
            'last_seen_ip' => '10.0.0.1',
        ]);
    }
}
