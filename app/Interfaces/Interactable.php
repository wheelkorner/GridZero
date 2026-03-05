<?php

namespace App\Interfaces;

/**
 * @property int $id
 */
interface Interactable
{
    /**
     * Define a dificuldade do alvo para o sistema de cálculo de tempo.
     */
    public function getDifficulty(): int;
}