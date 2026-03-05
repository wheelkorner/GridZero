<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulateNpcActivity extends Command
{
    protected $signature = 'npc:simulate';
    protected $description = 'Simulates NPC online activity: randomizes vulnerability windows and last_seen_at.';

    // Percentage of NPCs that will be "hacking" (vulnerable) at any given tick
    protected const HACK_PROBABILITY = 0.40; // 40% chance each NPC is mid-hack

    // Vulnerability window range in seconds
    protected const MIN_VULN_SECONDS = 20;
    protected const MAX_VULN_SECONDS = 120;

    public function handle(): void
    {
        $npcs = User::where('is_npc', true)->get();

        foreach ($npcs as $npc) {
            $isHacking = (mt_rand(1, 100) / 100) <= self::HACK_PROBABILITY;

            // Always mark NPCs as recently "seen" (online)
            $lastSeen = Carbon::now()->subSeconds(mt_rand(0, 60));

            if ($isHacking) {
                // Random vulnerability window between MIN and MAX
                $vulnSeconds = mt_rand(self::MIN_VULN_SECONDS, self::MAX_VULN_SECONDS);
                $vulnerableUntil = Carbon::now()->addSeconds($vulnSeconds);

                $npc->update([
                    'last_seen_at' => $lastSeen,
                    'vulnerable_until' => $vulnerableUntil,
                ]);
            } else {
                // NPC is idle/safe — clear any expired window
                $npc->update([
                    'last_seen_at' => $lastSeen,
                    'vulnerable_until' => null,
                ]);
            }
        }

        $count = $npcs->count();
        $this->info("NPC simulation tick: {$count} NPCs updated.");
    }
}
