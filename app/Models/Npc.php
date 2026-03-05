<?php

namespace App\Models;

use App\Interfaces\Interactable;
use Illuminate\Database\Eloquent\Model;

class Npc extends Model implements Interactable
{
    // Exemplo: NPC tem uma lógica de nível
    public function getDifficulty(): int
    {
        return $this->level * 2; // NPC é sempre 2x mais difícil que seu nível
    }
}