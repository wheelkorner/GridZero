<?php

namespace App\Interfaces;

interface Interactable
{
    /**
     * Define a dificuldade do alvo para o sistema de cálculo de tempo.
     */
    public function getDifficulty(): int;
}