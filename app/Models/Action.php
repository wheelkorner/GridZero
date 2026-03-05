<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'node_id',
        'type',
        'status',
        'started_at',
        'ends_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function interactable()
    {
        return $this->morphTo();
    }

    public function interactable()
    {
        return $this->morphTo();
    }
}
