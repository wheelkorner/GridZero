<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    /**
     * The full program catalog. This is the single source of truth for all shop items.
     */
    public static function catalog(): array
    {
        return [
            [
                'id' => 'imperson',
                'name' => 'Impersonator v2',
                'price' => 500,
                'description' => 'Usa credenciais capturadas para drenar créditos da vítima.',
                'usage' => 'shop use imperson <username>',
                'tags' => ['credentials', 'credits'],
            ],
            [
                'id' => 'keylog',
                'name' => 'KeyLogger.so',
                'price' => 300,
                'description' => 'Aumenta o XP roubado nos próximos 3 hacks em 50%.',
                'usage' => 'shop use keylog',
                'tags' => ['xp', 'passive'],
            ],
            [
                'id' => 'stealth',
                'name' => 'Stealth.bin',
                'price' => 400,
                'description' => 'Reduz pela metade o tempo de vulnerabilidade ao atacar.',
                'usage' => 'shop use stealth',
                'tags' => ['defense', 'passive'],
            ],
            [
                'id' => 'cracker',
                'name' => 'CrackForce.py',
                'price' => 600,
                'description' => 'Remove o custo de energia no próximo hack.',
                'usage' => 'shop use cracker',
                'tags' => ['energy', 'hack'],
            ],
            [
                'id' => 'siphon',
                'name' => 'DataSiphon.db',
                'price' => 450,
                'description' => 'Copia 2 arquivos por operação cp no terminal remoto.',
                'usage' => 'shop use siphon',
                'tags' => ['data', 'remote'],
            ],
            [
                'id' => 'firewall',
                'name' => 'FireWall-X',
                'price' => 700,
                'description' => 'Bloqueia o próximo ataque NPC que tentar invadir seu sistema.',
                'usage' => 'shop use firewall',
                'tags' => ['defense', 'protection'],
            ],
            [
                'id' => 'antivirus',
                'name' => 'AntiVirus Pro',
                'price' => 350,
                'description' => 'Remove vírus e trojans ativos. Fecha janela de vulnerabilidade.',
                'usage' => 'shop use antivirus',
                'tags' => ['defense', 'cleanup'],
            ],
            [
                'id' => 'virus',
                'name' => 'DataVirus.exe',
                'price' => 480,
                'description' => 'Infecta o alvo: drena energia por ciclo até ele usar antivirus.',
                'usage' => 'shop use virus <username>',
                'tags' => ['attack', 'persistent'],
            ],
            [
                'id' => 'trojan',
                'name' => 'TrojanHorse.bin',
                'price' => 550,
                'description' => 'Mantém portas do alvo abertas por 5 min (visível no scan).',
                'usage' => 'shop use trojan <username>',
                'tags' => ['attack', 'exploit'],
            ],
        ];
    }

    /**
     * List the shop catalog.
     */
    public function index(): JsonResponse
    {
        return response()->json(['catalog' => self::catalog()]);
    }

    /**
     * Purchase a program, deducting credits from the player's stats.
     */
    public function buy(Request $request): JsonResponse
    {
        $validated = $request->validate(['program_id' => 'required|string']);
        $programId = $validated['program_id'];

        $item = collect(self::catalog())->firstWhere('id', $programId);
        if (!$item) {
            return response()->json(['message' => 'PROGRAMA NÃO ENCONTRADO NO CATÁLOGO.'], 404);
        }

        /** @var User $user */
        $user = $request->user();
        $stats = $user->stats ?? [];
        $credits = $stats['credits'] ?? 0;
        $inventory = $stats['inventory'] ?? [];

        if (in_array($programId, $inventory)) {
            return response()->json(['message' => 'PROGRAMA JÁ INSTALADO.'], 409);
        }

        if ($credits < $item['price']) {
            return response()->json([
                'message' => "CRÉDITOS INSUFICIENTES. NECESSÁRIO: {$item['price']} CR.",
                'have' => $credits,
            ], 402);
        }

        $stats['credits'] = $credits - $item['price'];
        $stats['inventory'] = [...$inventory, $programId];
        $user->update(['stats' => $stats]);

        return response()->json([
            'message' => "PROGRAMA {$item['name']} INSTALADO COM SUCESSO.",
            'credits' => $stats['credits'],
            'inventory' => $stats['inventory'],
        ]);
    }

    /**
     * Execute/use a purchased program.
     */
    public function use(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|string',
            'target' => 'nullable|string',
        ]);

        /** @var User $player */
        $player = $request->user();
        $stats = $player->stats ?? [];
        $inventory = $stats['inventory'] ?? [];

        if (!in_array($validated['program_id'], $inventory)) {
            return response()->json(['message' => 'PROGRAMA NÃO INSTALADO. USE: shop buy ' . $validated['program_id']], 403);
        }

        return match ($validated['program_id']) {
            'imperson' => $this->useImperson($player, $validated['target'] ?? null),
            'keylog' => $this->useKeylog($player),
            'stealth' => $this->useStealth($player),
            'cracker' => $this->useCracker($player),
            'siphon' => $this->useSiphon($player),
            'firewall' => $this->useFirewall($player),
            'antivirus' => $this->useAntivirus($player),
            'virus' => $this->useVirus($player, $validated['target'] ?? null),
            'trojan' => $this->useTrojan($player, $validated['target'] ?? null),
            default => response()->json(['message' => 'PROGRAMA INVÁLIDO.'], 400),
        };
    }

    // ---- Program Effects -----------------------------------------------

    /**
     * imperson: Transfer 10–30% of target's credits to player.
     * Target must exist and not be the player themselves.
     */
    private function useImperson(User $player, ?string $targetUsername): JsonResponse
    {
        if (!$targetUsername) {
            return response()->json(['message' => 'USO: shop use imperson <username>'], 400);
        }

        $target = User::where('username', $targetUsername)->first();
        if (!$target || $target->id === $player->id) {
            return response()->json(['message' => 'ALVO NÃO ENCONTRADO OU INVÁLIDO.'], 404);
        }

        $targetStats = $target->stats ?? [];
        $targetCredits = $targetStats['credits'] ?? 0;

        if ($targetCredits <= 0) {
            return response()->json(['message' => "ALVO SEM CRÉDITOS. OPERAÇÃO ABORTADA."], 422);
        }

        $pct = mt_rand(10, 30) / 100;
        $stolen = (int) ceil($targetCredits * $pct);

        // Deduct from target
        $targetStats['credits'] = max(0, $targetCredits - $stolen);
        $target->update(['stats' => $targetStats]);

        // Add to player
        $playerStats = $player->stats ?? [];
        $playerStats['credits'] = ($playerStats['credits'] ?? 0) + $stolen;
        $player->update(['stats' => $playerStats]);

        return response()->json([
            'message' => "TRANSFERÊNCIA CONCLUÍDA: +{$stolen} CR DE {$targetUsername}.",
            'stolen' => $stolen,
            'your_credits' => $playerStats['credits'],
        ]);
    }

    /** keylog: Activate XP bonus for next 3 hacks. */
    private function useKeylog(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        $stats['keylog_charges'] = 3;
        $player->update(['stats' => $stats]);
        return response()->json(['message' => 'KEYLOGGER ATIVADO. PRÓXIMOS 3 HACKS: +50% XP.']);
    }

    /** stealth: Set a flag so the next action creates a shorter vulnerability window. */
    private function useStealth(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        $stats['stealth_active'] = true;
        $player->update(['stats' => $stats]);
        return response()->json(['message' => 'STEALTH.BIN ATIVO. PRÓXIMO HACK: JANELA DE VULNERABILIDADE REDUZIDA.']);
    }

    /** cracker: Skip energy cost on the next hack. */
    private function useCracker(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        $stats['cracker_active'] = true;
        $player->update(['stats' => $stats]);
        return response()->json(['message' => 'CRACKFORCE.PY PRONTO. PRÓXIMO HACK: SEM CUSTO DE ENERGIA.']);
    }

    /** siphon: Enable 2-file cp on next remote terminal session. */
    private function useSiphon(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        $stats['siphon_charges'] = 2;
        $player->update(['stats' => $stats]);
        return response()->json(['message' => 'DATASIPHON.DB CARREGADO. PRÓXIMO TERMINAL REMOTO: CP DUPLO ATIVO.']);
    }

    /** firewall: Block the next incoming NPC attack. */
    private function useFirewall(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        $stats['firewall_active'] = true;
        $player->update(['stats' => $stats]);
        return response()->json(['message' => 'FIREWALL-X ATIVADO. PRÓXIMO ATAQUE NPC SERÁ BLOQUEADO.']);
    }

    /** antivirus: Clear virus/trojan flags and close vulnerability window. */
    private function useAntivirus(User $player): JsonResponse
    {
        $stats = $player->stats ?? [];
        unset($stats['virus_infected'], $stats['trojan_planted']);
        $player->update([
            'stats' => $stats,
            'vulnerable_until' => null,
        ]);
        return response()->json(['message' => 'ANTIVIRUS PRO: SISTEMA LIMPO. JANELA DE VULNERABILIDADE FECHADA.']);
    }

    /** virus: Infect a target — drain 5–15 energy per NPC simulation tick. */
    private function useVirus(User $player, ?string $targetUsername): JsonResponse
    {
        if (!$targetUsername) {
            return response()->json(['message' => 'USO: shop use virus <username>'], 400);
        }
        $target = User::where('username', $targetUsername)->where('id', '!=', $player->id)->first();
        if (!$target) {
            return response()->json(['message' => 'ALVO NÃO ENCONTRADO.'], 404);
        }
        $tStats = $target->stats ?? [];
        $tStats['virus_infected'] = true;
        $target->update(['stats' => $tStats]);
        return response()->json(['message' => "DATAVIRUS.EXE IMPLANTADO EM {$targetUsername}. DRENAGEM DE ENERGIA INICIADA."]);
    }

    /** trojan: Force target's vulnerability window open for 5 minutes. */
    private function useTrojan(User $player, ?string $targetUsername): JsonResponse
    {
        if (!$targetUsername) {
            return response()->json(['message' => 'USO: shop use trojan <username>'], 400);
        }
        $target = User::where('username', $targetUsername)->where('id', '!=', $player->id)->first();
        if (!$target) {
            return response()->json(['message' => 'ALVO NÃO ENCONTRADO.'], 404);
        }
        $target->update(['vulnerable_until' => \Carbon\Carbon::now()->addMinutes(5)]);
        return response()->json(['message' => "TROJAN ATIVADO: {$targetUsername} PERMANECERÁ EXPOSTÓ POR 5 MINUTOS."]);
    }
}
