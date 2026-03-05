<?php

namespace App\Http\Controllers;

use App\Services\ActionService;
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
            $action = $this->actionService->startAction($user, $validated['type'], $validated['node_id']);

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
}
