<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'stats',
        'role',
        'last_seen_at',
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
            'stats' => 'array',
            'password' => 'hashed',
        ];
    }

    protected $appends = ['pending_action'];

    /**
     * Get the actions for the user.
     */
    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    /**
     * Accessor for the current pending action.
     */
    public function getPendingActionAttribute()
    {
        return $this->actions()->where('status', 'pending')->first();
    }
}
