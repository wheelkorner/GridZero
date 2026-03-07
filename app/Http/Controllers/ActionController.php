<?php

namespace App\Http\Controllers;

use App\Services\ActionService;
use App\Services\ScanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ActionController extends Controller
{
    protected $actionService;

    public function __construct(ActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Store a new action for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'node_id' => 'required|integer|exists:nodes,id',
        ]);

        try {
            $user = $request->user();
            /** @var \App\Models\Node $target */
            $target = \App\Models\Node::findOrFail($validated['node_id']);
            $action = $this->actionService->startAction($user, $validated['type'], $target);

            return response()->json([
                'message' => 'Ação iniciada com sucesso.',
                'data' => $action,
                'user' => [
                    'energy_points' => $user->energy_points,
                    'last_energy_update' => $user->last_energy_update,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 400);
        }
    }

    /**
     * Perform a network scan to find vulnerable targets.
     */
    public function scan(ScanService $scanService): JsonResponse
    {
        return response()->json([
            'targets' => $scanService->getScanTargets()
        ]);
    }

    /**
     * Attempt to connect to a vulnerable user by IP address.
     * Returns the NPC profile and a generated VFS for the remote terminal.
     */
    public function connectByIp(Request $request): JsonResponse
    {
        $validated = $request->validate(['ip' => 'required|string']);

        $target = \App\Models\User::where('last_seen_ip', $validated['ip'])->first();

        if (!$target) {
            return response()->json(['message' => 'HOST NÃO ENCONTRADO. IP INVÁLIDO.'], 404);
        }

        if (!$target->vulnerable_until || !$target->vulnerable_until->isFuture()) {
            return response()->json(['message' => 'CONEXÃO RECUSADA: PORTAS FECHADAS.'], 403);
        }

        // Generate a deterministic VFS for the NPC based on their username and level
        $vfs = $this->generateNpcVfs($target);

        // Opening a connection also makes the attacker vulnerable
        $request->user()->update([
            'vulnerable_until' => \Carbon\Carbon::now()->addSeconds(60),
        ]);

        return response()->json([
            'hostname' => $target->username,
            'level' => $target->level,
            'ip' => $target->last_seen_ip,
            'vulnerable_until' => $target->vulnerable_until,
            'vfs' => $vfs,
        ]);
    }

    /**
     * Generates a virtual filesystem for an NPC target.
     */
    private function generateNpcVfs(\App\Models\User $npc): array
    {
        return $npc->generateVfs();
    }
}
