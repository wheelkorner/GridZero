<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EliteHackerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elites = [
            [
                'alias' => 'NullPtr',
                'archetype' => 'Infiltrador',
                'profile' => 'Especialista em corrupção de memória e bypass.',
                'level' => 110,
            ],
            [
                'alias' => 'VoidGhost',
                'archetype' => 'Fantasma',
                'profile' => 'Ninguém sabe se é um script ou humano; atua no escuro.',
                'level' => 120,
            ],
            [
                'alias' => 'RootAccess',
                'archetype' => 'Agressor',
                'profile' => 'Focado em invasões diretas e brute force.',
                'level' => 105,
            ],
            [
                'alias' => 'CipherX',
                'archetype' => 'Criptógrafo',
                'profile' => 'Bloqueia sistemas com chaves impossíveis de quebrar.',
                'level' => 140,
            ],
            [
                'alias' => 'GlitchWraith',
                'archetype' => 'Disruptivo',
                'profile' => 'Especialista em timing attacks e micro-erros.',
                'level' => 115,
            ],
            [
                'alias' => 'NetTerror',
                'archetype' => 'Botnet Master',
                'profile' => 'Controla exércitos de dispositivos IoT.',
                'level' => 130,
            ],
            [
                'alias' => 'BinaryExecution',
                'archetype' => 'Executor',
                'profile' => 'Rápido, preciso e sem deixar rastros.',
                'level' => 135,
            ],
            [
                'alias' => 'SysFault',
                'archetype' => 'Sabotador',
                'profile' => 'Especialista em criar condições de corrida (Race Conditions).',
                'level' => 118,
            ],
            [
                'alias' => 'KernelPanic',
                'archetype' => 'Caótico',
                'profile' => 'Derruba sistemas inteiros apenas pelo prazer de ver o log de erro.',
                'level' => 150,
            ],
            [
                'alias' => 'DarkLogic',
                'archetype' => 'Analista',
                'profile' => 'Vende vulnerabilidades 0-day no mercado negro.',
                'level' => 145,
            ],
        ];

        foreach ($elites as $data) {
            // Calculate advanced hardware for elites
            $cpu = 2000 + ($data['level'] * 100);
            $ram = 4096 + ($data['level'] * 64);

            // Random IP in a specific range for elites
            $ip = "10.0." . rand(1, 254) . "." . rand(1, 254);
            $reputation = 5000 + ($data['level'] * 50);

            User::updateOrCreate(
                ['username' => $data['alias']],
                [
                    'password' => Hash::make('elite_password_secure'),
                    'level' => $data['level'],
                    'reputation_score' => $reputation,
                    'energy_points' => 100,
                    'cpu' => $cpu,
                    'ram' => $ram,
                    'ssd' => 100,
                    'role' => 'player',
                    'is_npc' => true,
                    'stats' => [
                        'archetype' => $data['archetype'],
                        'profile' => $data['profile'],
                        'xp' => $data['level'] * 2500,
                        'credits' => $data['level'] * 50,
                    ],
                    'last_energy_update' => Carbon::now(),
                    'last_seen_ip' => $ip,
                ]
            );
        }
    }
}
