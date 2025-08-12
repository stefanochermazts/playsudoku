<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Contracts;

use App\Domain\Sudoku\Grid;

/**
 * Interfaccia per i calcolatori di difficoltà dei puzzle Sudoku
 */
interface DifficultyRaterInterface
{
    /**
     * Calcola la difficoltà di un puzzle
     */
    public function rateDifficulty(Grid $puzzle): string;

    /**
     * Calcola un punteggio numerico di difficoltà
     */
    public function getScore(Grid $puzzle): int;

    /**
     * Ottiene una analisi dettagliata della difficoltà
     * 
     * @return array<string, mixed>
     */
    public function getDetailedAnalysis(Grid $puzzle): array;
}
