<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdditionalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'ZeroDay_Hunter', 'email' => 'hunter@gridzero.dev', 'skill' => 95],
            ['name' => 'Shadow_Runner', 'email' => 'shadow@gridzero.dev', 'skill' => 88],
            ['name' => 'Packet_Sniffer', 'email' => 'sniffer@gridzero.dev', 'skill' => 72],
            ['name' => 'Brute_Force', 'email' => 'brute@gridzero.dev', 'skill' => 65],
            ['name' => 'Logic_Bomb', 'email' => 'bomb@gridzero.dev', 'skill' => 91],
            ['name' => 'Proxy_Master', 'email' => 'proxy@gridzero.dev', 'skill' => 80],
            ['name' => 'Script_Kiddie', 'email' => 'kiddie@gridzero.dev', 'skill' => 20],
            ['name' => 'Kernel_Seeker', 'email' => 'kernel@gridzero.dev', 'skill' => 85],
            ['name' => 'Backdoor_King', 'email' => 'king@gridzero.dev', 'skill' => 78],
            ['name' => 'Firewall_Breaker', 'email' => 'breaker@gridzero.dev', 'skill' => 93],
        ];

        foreach ($users as $userData) {
            // Mapping:
            // name  -> username
            // skill -> level
            // email -> stats.email

            // Calculate hardware based on skill level (higher skill = better hardware)
            $cpu = 800 + ($userData['skill'] * 50);
            $ram = 512 + ($userData['skill'] * 32);
            $ssd = 100;

            // Random IP for additional users
            $ip = rand(172, 192) . "." . rand(0, 255) . "." . rand(0, 255) . "." . rand(1, 254);
            $reputation = $userData['skill'] * 50;

            User::updateOrCreate(
                ['username' => $userData['name']],
                [
                    'password' => Hash::make('password123'),
                    'level' => $userData['skill'],
                    'reputation_score' => $reputation,
                    'energy_points' => 100,
                    'cpu' => $cpu,
                    'ram' => $ram,
                    'ssd' => $ssd,
                    'role' => 'player',
                    'is_npc' => true,
                    'stats' => [
                        'email' => $userData['email'],
                        'xp' => $userData['skill'] * 1000,
                        'credits' => $userData['skill'] * 10,
                    ],
                    'last_energy_update' => now(),
                    'last_seen_ip' => $ip,
                ]
            );
        }
    }
}
