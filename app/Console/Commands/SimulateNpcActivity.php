<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulateNpcActivity extends Command
{
    protected $signature = 'npc:simulate {--loop : Run in an infinite loop for development}';
    protected $description = 'Simulates NPC online activity with variability (Active, Scanning, Idle) and growth.';

    public function handle(): void
    {
        $this->info("NPC simulation started.");

        do {
            $npcs = User::where('is_npc', true)->get();
            $now = Carbon::now();

            foreach ($npcs as $npc) {
                $roll = mt_rand(1, 100);

                // 10% chance to level up if they were "active" (simulated growth)
                if ($roll > 90 && $npc->level < 100) {
                    $npc->increment('level');
                }

                // Randomize State
                if ($roll <= 30) { // 30% Active / Attacking
                    $npc->update([
                        'last_seen_at' => $now,
                        'vulnerable_until' => $now->copy()->addSeconds(mt_rand(60, 300)),
                    ]);
                } elseif ($roll <= 60) { // 30% Scanning / Short Window
                    $npc->update([
                        'last_seen_at' => $now,
                        'vulnerable_until' => $now->copy()->addSeconds(mt_rand(10, 30)),
                    ]);
                } else { // 40% Idle / Safe
                    $npc->update([
                        'last_seen_at' => $now,
                        'vulnerable_until' => null,
                    ]);
                }
            }

            $this->info($now->format('H:i:s') . " - NPC tick: {$npcs->count()} NPCs updated with variable states.");

            if ($this->option('loop')) {
                sleep(30); // Run every 30s in loop mode
            }
        } while ($this->option('loop'));
    }
}
