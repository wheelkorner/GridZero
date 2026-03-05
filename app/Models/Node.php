<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Interfaces\Interactable; // Importe a interface
use Illuminate\Database\Eloquent\Model;

class Node extends Model implements Interactable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'difficulty',
        'reward_multiplier',
    ];

    public function getDifficulty(): int
    {
        return $this->difficulty; // Retorna o campo da tabela nodes
    }
}
