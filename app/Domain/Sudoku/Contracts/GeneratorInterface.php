<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Contracts;

use App\Domain\Sudoku\Grid;

/**
 * Interfaccia per i generatori di puzzle Sudoku
 */
interface GeneratorInterface
{
    /**
     * Genera un puzzle completo (griglia risolta)
     */
    public function generateCompleteGrid(int $seed): Grid;

    /**
     * Genera un puzzle con il numero specificato di celle given
     */
    public function generatePuzzle(int $seed, int $givens = 25): Grid;

    /**
     * Genera un puzzle con difficoltà specifica
     */
    public function generatePuzzleWithDifficulty(int $seed, string $difficulty): Grid;

    /**
     * Imposta il seed per la generazione deterministica
     */
    public function setSeed(int $seed): void;
}
