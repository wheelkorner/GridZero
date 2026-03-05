<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle the admin login request.
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended('/admin');
            }

            Auth::logout();
            return back()->withErrors(['username' => 'Apenas administradores podem acessar esta área.']);
        }

        return back()->withErrors(['username' => 'Credenciais inválidas.']);
    }

    /**
     * Show the administrative dashboard.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $totalPlayers = User::where('role', 'player')->count();
        $onlineCount = User::where('last_seen_at', '>', Carbon::now()->subMinutes(5))->count();
        $activeActions = \App\Models\Action::where('status', 'pending')->count();

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('totalPlayers', 'onlineCount', 'activeActions', 'recentUsers'));
    }

    /**
     * View all operators (Blade-based management).
     */
    public function viewUsers()
    {
        $this->authorizeAdmin();
        $users = User::all()->map(function (User $user) {
            $lastSeen = $user->last_seen_at;
            $user->is_online = $lastSeen instanceof Carbon && $lastSeen->gt(Carbon::now()->subMinutes(5));
            return $user;
        });

        return view('admin.users', compact('users'));
    }

    /** Show detailed profile of a single user. */
    public function showUser(int $id)
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);
        $lastSeen = $user->last_seen_at;
        $user->is_online = $lastSeen instanceof Carbon && $lastSeen->gt(Carbon::now()->subMinutes(5));
        return view('admin.users.show', compact('user'));
    }

    /** Show edit form for a single user. */
    public function editUser(int $id)
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /** Handle update form submission. */
    public function updateUser(Request $request, int $id)
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:60|unique:users,username,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'level' => 'required|integer|min:1|max:999',
            'role' => 'required|in:player,admin',
            'password' => 'nullable|min:6',
            'cpu' => 'nullable|integer|min:1',
            'ram' => 'nullable|integer|min:1',
            'ssd' => 'nullable|integer|min:1',
            'energy_points' => 'nullable|integer|min:0|max:1000',
        ]);

        $fillable = [
            'username' => $validated['username'],
            'email' => $validated['email'] ?? $user->email,
            'level' => $validated['level'],
            'role' => $validated['role'],
            'cpu' => $validated['cpu'] ?? $user->cpu,
            'ram' => $validated['ram'] ?? $user->ram,
            'ssd' => $validated['ssd'] ?? $user->ssd,
            'energy_points' => $validated['energy_points'] ?? $user->energy_points,
        ];

        if (!empty($validated['password'])) {
            $fillable['password'] = Hash::make($validated['password']);
        }

        $user->update($fillable);

        return redirect()->route('admin.users.show', $id)
            ->with('success', "Operador {$user->username} atualizado com sucesso.");
    }

    /**
     * View all network nodes (Blade-based management).
     */
    public function viewNodes()
    {
        $this->authorizeAdmin();
        $nodes = \App\Models\Node::all();
        return view('admin.nodes', compact('nodes'));
    }

    /**
     * View all ongoing and completed actions.
     */
    public function viewActions()
    {
        $this->authorizeAdmin();
        $actions = \App\Models\Action::with(['user', 'node'])->orderBy('created_at', 'desc')->take(100)->get();
        return view('admin.actions', compact('actions'));
    }

    /**
     * List all users with their online status.
     * 
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        $this->authorizeAdmin();

        $users = User::all()->map(function (User $user) {
            $lastSeen = $user->last_seen_at;
            $isOnline = $lastSeen instanceof Carbon && $lastSeen->gt(Carbon::now()->subMinutes(5));
            return [
                'id' => $user->id,
                'username' => $user->username,
                'level' => $user->level,
                'role' => $user->role,
                'is_npc' => $user->is_npc,
                'is_online' => $isOnline,
                'last_seen' => $lastSeen instanceof Carbon ? $lastSeen->diffForHumans() : 'Never',
            ];
        });

        return response()->json($users);
    }

    /**
     * Get detailed info for a specific player.
     * 
     * @param string $username
     * @return JsonResponse
     */
    public function userInfo(string $username): JsonResponse
    {
        $this->authorizeAdmin();

        /** @var User $user */
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'username' => $user->username,
            'level' => $user->level,
            'role' => $user->role,
            'is_npc' => $user->is_npc,
            'cpu' => $user->cpu,
            'ram' => $user->ram,
            'ssd' => $user->ssd,
            'energy' => $user->energy_points,
            'stats' => $user->stats,
            'last_seen' => $user->last_seen_at instanceof Carbon ? $user->last_seen_at->toDateTimeString() : 'Never',
        ]);
    }

    /**
     * Impersonate another user.
     * 
     * @param string $username
     * @return JsonResponse
     */
    public function impersonate(string $username): JsonResponse
    {
        $this->authorizeAdmin();

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->username === $username) {
            return response()->json(['error' => 'You cannot impersonate yourself'], 422);
        }

        /** @var User $userToImpersonate */
        $userToImpersonate = User::where('username', $username)->firstOrFail();

        return response()->json([
            'message' => "Impersonating {$username}",
            'user' => $userToImpersonate
        ]);
    }

    /**
     * Middleware check for admin.
     * 
     * @return void
     */
    private function authorizeAdmin(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'SUDO MODE REQUIRED.');
        }
    }
}
