<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Interfaces\Interactable;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $level
 * @property int $cpu
 * @property int $ram
 * @property int $ssd
 * @property int $energy_points
 * @property \Carbon\Carbon|null $last_energy_update
 * @property int $reputation_score
 * @property string|null $last_seen_ip
 * @property array|null $stats
 * @property string $role
 * @property bool $is_npc
 * @property \Carbon\Carbon|null $vulnerable_until
 * @property \Carbon\Carbon|null $last_seen_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Action> $actions
 * @property-read \App\Models\Action|null $pending_action
 */
class User extends Authenticatable implements Interactable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'level',
        'cpu',
        'ram',
        'ssd',
        'energy_points',
        'last_energy_update',
        'reputation_score',
        'stats',
        'role',
        'is_npc',
        'vulnerable_until',
        'last_seen_at',
        'last_seen_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_energy_update' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_npc' => 'boolean',
            'vulnerable_until' => 'datetime',
            'stats' => 'array',
            'password' => 'hashed',
            'last_seen_ip' => 'string',
            'reputation_score' => 'integer',
            'level' => 'integer',
        ];
    }

    protected $appends = ['pending_action'];

    /**
     * Get the actions for the user.
     *
     * @return HasMany<Action, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    /**
     * Accessor for the current pending action.
     *
     * @return Action|null
     */
    public function getPendingActionAttribute()
    {
        return $this->actions()->where('status', 'pending')->first();
    }

    /**
     * Interface Interactable: Retorna a dificuldade baseada no nível.
     */
    public function getDifficulty(): int
    {
        return (int) floor($this->level / 10) ?: 1;
    }
}
