<?php

namespace App\Services;

use App\Models\User;
use App\Models\Action;
use App\Interfaces\Interactable;
use Illuminate\Support\Facades\DB;
use App\Jobs\CompleteActionJob;
use Carbon\Carbon;

class ActionService
{
    protected const REGEN_RATE_MINUTES = 5;
    protected const ENERGY_PER_REGEN = 1;
    protected const ACTION_ENERGY_COST = 10;

    /**
     * Calcula e atualiza a energia regenerada do usuário.
     */
    public function calculateEnergy(User $user): void
    {
        $now = Carbon::now();
        $lastUpdate = $user->last_energy_update ?? $user->created_at;

        $diffInMinutes = $lastUpdate->diffInMinutes($now);

        // Se ainda não passou o tempo mínimo de regeneração, não faz nada
        if ($diffInMinutes < self::REGEN_RATE_MINUTES) {
            return;
        }

        $regenerationAmount = floor($diffInMinutes / self::REGEN_RATE_MINUTES) * self::ENERGY_PER_REGEN;
        $newEnergy = min(100, $user->energy_points + $regenerationAmount);

        // Ajusta o timestamp para o resto da divisão para não "perder" segundos na conta
        $minutesToSubtract = $diffInMinutes % self::REGEN_RATE_MINUTES;
        $newLastUpdate = $now->subMinutes($minutesToSubtract);

        $user->update([
            'energy_points' => $newEnergy,
            'last_energy_update' => $newLastUpdate,
        ]);

        // Recarrega o objeto para garantir que os valores na memória estejam frescos
        $user->refresh();
    }

    /**
     * Inicia uma nova ação para o usuário vinculada a um alvo (Interactable).
     * * @param User $user
     * @param string $type
     * @param Interactable $target (Node, Npc, etc)
     * @return Action
     */
    public function startAction(User $user, string $type, Interactable $target): Action
    {
        return DB::transaction(function () use ($user, $type, $target) {
            // 1. Atualiza energia antes de validar
            $this->calculateEnergy($user);

            // 2. Validações
            if ($user->energy_points < self::ACTION_ENERGY_COST) {
                throw new \Exception("Energia insuficiente.");
            }

            if ($user->actions()->where('status', 'pending')->exists()) {
                throw new \Exception("Já existe uma ação pendente.");
            }

            // 3. Execução
            $user->decrement('energy_points', self::ACTION_ENERGY_COST);

            // Usa a interface para obter a dificuldade dinamicamente
            $durationSeconds = $target->getDifficulty() * 120;
            $endsAt = Carbon::now()->addSeconds($durationSeconds);

            $action = $user->actions()->create([
                'interactable_type' => get_class($target), // Armazena o namespace completo do Model
                'interactable_id' => $target->id,
                'type' => $type,
                'status' => 'pending',
                'started_at' => Carbon::now(),
                'ends_at' => $endsAt,
            ]);

            // Despacha o Job
            CompleteActionJob::dispatch($action->id)->delay($endsAt);

            return $action;
        });
    }
}