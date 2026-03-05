<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $node_id
 * @property string|null $interactable_type
 * @property int|null $interactable_id
 * @property string $type
 * @property string $status
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Node|null $node
 * @property-read \Illuminate\Database\Eloquent\Model|null $interactable
 */
class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'node_id',
        'interactable_type',
        'interactable_id',
        'type',
        'status',
        'started_at',
        'ends_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário criador da ação.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento específico com Nó (legado/auxiliar).
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * Relacionamento polimórfico com o alvo (Node, Npc, etc).
     */
    public function interactable(): MorphTo
    {
        return $this->morphTo();
    }
}
