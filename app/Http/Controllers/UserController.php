<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get the authenticated user with stats.
     */
    public function show()
    {
        $user = Auth::user();
        $user->update(['last_seen_at' => now()]);
        return response()->json($user->loadCount('actions'));
    }

    /**
     * Update user stats (like VFS persistence).
     */
    public function updateStats(Request $request)
    {
        $request->validate([
            'stats' => 'required|array',
        ]);

        $user = $request->user();

        // Merge with existing stats to prevent data loss
        $incoming = $request->stats;

        // Ensure credits is always a whole integer
        if (isset($incoming['credits'])) {
            $incoming['credits'] = (int) floor($incoming['credits']);
        }

        $currentStats = $user->stats ?? [];
        $user->stats = array_merge($currentStats, $incoming);

        // Also floor reputation_score if present
        if (isset($user->reputation_score)) {
            $user->reputation_score = (int) $user->reputation_score;
        }

        $user->save();

        return response()->json([
            'message' => 'Stats updated successfully.',
            'stats' => $user->stats,
        ]);
    }
}
