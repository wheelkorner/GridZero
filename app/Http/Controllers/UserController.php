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
        $currentStats = $user->stats ?? [];
        $user->stats = array_merge($currentStats, $request->stats);

        $user->save();

        return response()->json([
            'message' => 'Stats updated successfully.',
            'stats' => $user->stats,
        ]);
    }
}
