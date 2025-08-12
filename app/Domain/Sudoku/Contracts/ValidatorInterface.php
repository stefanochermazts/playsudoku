<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Contracts;

use App\Domain\Sudoku\Grid;

/**
 * Interfaccia per i validatori di griglie Sudoku
 */
interface ValidatorInterface
{
    /**
     * Verifica se una griglia è valida (nessun conflitto)
     */
    public function isValid(Grid $grid): bool;

    /**
     * Verifica se una griglia ha una soluzione unica
     */
    public function hasUniqueSolution(Grid $grid): bool;

    /**
     * Ottiene tutti gli errori di validazione in una griglia
     * 
     * @return array<string, mixed>
     */
    public function getValidationErrors(Grid $grid): array;

    /**
     * Verifica se un valore può essere inserito in una specifica posizione
     */
    public function canPlaceValue(Grid $grid, int $row, int $col, int $value): bool;
}
