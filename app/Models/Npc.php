<?php

namespace App\Models;

use App\Interfaces\Interactable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $level
 */
class Npc extends Model implements Interactable
{
    /**
     * Define a dificuldade do NPC.
     */
    public function getDifficulty(): int
    {
        return $this->level * 2; // NPC é sempre 2x mais difícil que seu nível
    }
}