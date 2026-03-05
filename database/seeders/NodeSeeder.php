<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodes = [
            ['name' => 'CORP_FIREWALL_ALPHA', 'difficulty' => 1, 'reward_multiplier' => 1.0],
            ['name' => 'BANK_PROXY_DELTA', 'difficulty' => 2, 'reward_multiplier' => 1.5],
            ['name' => 'DARKNET_NODE_SIGMA', 'difficulty' => 3, 'reward_multiplier' => 2.0],
            ['name' => 'MILITARY_HUB_OMEGA', 'difficulty' => 4, 'reward_multiplier' => 3.0],
            ['name' => 'QUANTUM_VAULT_ZERO', 'difficulty' => 5, 'reward_multiplier' => 5.0],
        ];

        foreach ($nodes as $node) {
            \App\Models\Node::create($node);
        }
    }
}
