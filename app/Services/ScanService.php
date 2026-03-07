<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class ScanService
{
    /**
     * Retorna a lista de alvos (outros usuários/NPCs) detectáveis na rede.
     * Filtra por usuários online ou que possuam uma janela de vulnerabilidade ativa.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getScanTargets()
    {
        return User::query()
            ->where(function ($query) {
                // Online nos últimos 5 minutos
                $query->where('last_seen_at', '>', Carbon::now()->subMinutes(5))
                    // Ou vulnerável (Counter-Hacking ativo)
                    ->orWhere('vulnerable_until', '>', Carbon::now())
                    // Ou se for um NPC (sempre online)
                    ->orWhere('is_npc', true);
            })
            ->where('id', '!=', auth()->id()) // Não escaneia a si mesmo
            ->get()
            ->map(function ($user) {
                $isVulnerable = $user->vulnerable_until && $user->vulnerable_until->isFuture();

                return [
                    'hostname' => $user->username,
                    'status' => 'ONLINE',
                    'ports' => $isVulnerable ? 'OPEN (VULNERABLE)' : 'CLOSED',
                    'vulnerability_window' => $isVulnerable
                        ? $user->vulnerable_until->diffForHumans(['parts' => 1])
                        : 'N/A',
                    'ip' => $user->last_seen_ip ?? '127.0.0.1'
                ];
            });
    }
}
