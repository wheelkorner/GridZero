<?php

namespace App\Services;

use App\Models\User;
use App\Models\Action;
use App\Models\Node;
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

        if ($diffInMinutes >= self::REGEN_RATE_MINUTES) {
            $regenerationAmount = floor($diffInMinutes / self::REGEN_RATE_MINUTES) * self::ENERGY_PER_REGEN;

            $newEnergy = min(100, $user->energy_points + $regenerationAmount);

            $minutesToSubtract = $diffInMinutes % self::REGEN_RATE_MINUTES;
            $newLastUpdate = $now->subMinutes($minutesToSubtract);

            $user->update([
                'energy_points' => $newEnergy,
                'last_energy_update' => $newLastUpdate,
            ]);
        }
    }

    /**
     * Inicia uma nova ação para o usuário vinculada a um nó.
     */
    public function startAction(User $user, string $type, int $nodeId): Action
    {
        return DB::transaction(function () use ($user, $type, $nodeId) {
            // 1. Atualiza energia antes de validar
            $this->calculateEnergy($user);

            // 2. Validações
            if ($user->energy_points < self::ACTION_ENERGY_COST) {
                throw new \Exception("Energia insuficiente.");
            }

            $hasPendingAction = $user->actions()
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingAction) {
                throw new \Exception("Já existe uma ação pendente ativa.");
            }

            $node = Node::findOrFail($nodeId);

            // 3. Execução
            $user->decrement('energy_points', self::ACTION_ENERGY_COST);

            // Duração baseada na dificuldade: cada nível = 2 minutos
            // Para testes rápidos, poderíamos usar segundos, mas vamos seguir a lógica de minutos.
            $durationSeconds = $node->difficulty * 120;
            $endsAt = Carbon::now()->addSeconds($durationSeconds);

            $action = $user->actions()->create([
                'node_id' => $node->id,
                'type' => $type,
                'status' => 'pending',
                'started_at' => Carbon::now(),
                'ends_at' => $endsAt,
            ]);

            // Despacha o Job para completar a ação de forma assíncrona
            CompleteActionJob::dispatch($action->id)->delay($endsAt);

            return $action;
        });
    }
}
