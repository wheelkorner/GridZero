<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * List all users with their online status.
     */
    public function users()
    {
        $this->authorizeAdmin();

        $users = User::all()->map(function ($user) {
            $isOnline = $user->last_seen_at && $user->last_seen_at->gt(Carbon::now()->subMinutes(5));
            return [
                'id' => $user->id,
                'username' => $user->username,
                'level' => $user->level,
                'role' => $user->role,
                'is_online' => $isOnline,
                'last_seen' => $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never',
            ];
        });

        return response()->json($users);
    }

    /**
     * Get detailed info for a specific player.
     */
    public function userInfo($username)
    {
        $this->authorizeAdmin();

        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'username' => $user->username,
            'level' => $user->level,
            'role' => $user->role,
            'cpu' => $user->cpu,
            'ram' => $user->ram,
            'ssd' => $user->ssd,
            'energy' => $user->energy_points,
            'stats' => $user->stats,
            'last_seen' => $user->last_seen_at ? $user->last_seen_at->toDateTimeString() : 'Never',
        ]);
    }

    /**
     * Impersonate another user.
     */
    public function impersonate($username)
    {
        $this->authorizeAdmin();

        if (Auth::user()->username === $username) {
            return response()->json(['error' => 'You cannot impersonate yourself'], 422);
        }

        $userToImpersonate = User::where('username', $username)->firstOrFail();

        // In a real session-based app, we'd swap the session. 
        // Here, we just return the user data so the frontend can "switch" the active user state.
        return response()->json([
            'message' => "Impersonating {$username}",
            'user' => $userToImpersonate
        ]);
    }

    /**
     * Middleware check for admin.
     */
    private function authorizeAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'SUDO MODE REQUIRED.');
        }
    }
}
