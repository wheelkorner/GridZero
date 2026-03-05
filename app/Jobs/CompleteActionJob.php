<?php

namespace App\Jobs;

use App\Models\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class CompleteActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $actionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $actionId)
    {
        $this->actionId = $actionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $action = Action::with(['user', 'node'])->find($this->actionId);

        if (!$action || $action->status !== 'pending') {
            return;
        }

        $now = Carbon::now();

        // Validação de tempo (margem de segurança de alguns segundos)
        if ($now->lt($action->ends_at->subSeconds(2))) {
            return;
        }

        // Lógica de Sucesso: Base 85% - (Dificuldade * 5%) + (Lvl * 2%)
        $difficultyPenalty = ($action->node->difficulty ?? 1) * 5;
        $levelBonus = $action->user->level * 2;
        $successChance = 85 - $difficultyPenalty + $levelBonus;

        $isSuccess = rand(1, 100) <= max(10, min(95, $successChance));

        if ($isSuccess) {
            $action->update(['status' => 'completed']);

            $user = $action->user;
            $node = $action->node;
            $multiplier = $node->reward_multiplier ?? 1.0;

            // Conceder Recompensas
            $stats = $user->stats ?? [];

            $gainedXP = round(100 * $multiplier);
            $gainedCredits = round(50 * $multiplier);

            $stats['xp'] = ($stats['xp'] ?? 0) + $gainedXP;
            $stats['credits'] = ($stats['credits'] ?? 0) + $gainedCredits;

            $newLevel = floor($stats['xp'] / 1000) + 1;

            // Hardware Scaling on Level Up
            $newCpu = $user->cpu;
            $newRam = $user->ram;

            if ($newLevel > $user->level) {
                $levelDiff = $newLevel - $user->level;
                $newCpu += $levelDiff * 200; // +200MHz per level
                $newRam += $levelDiff * 256; // +256MB per level
            }

            $user->update([
                'stats' => $stats,
                'level' => $newLevel,
                'cpu' => $newCpu,
                'ram' => $newRam,
            ]);
        } else {
            $action->update(['status' => 'failed']);

            // SSD Damage on failure (-10%)
            $user = $action->user;
            $newSsd = max(0, $user->ssd - 10);
            $user->update(['ssd' => $newSsd]);
        }
    }
}
