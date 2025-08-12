<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Contracts;

use App\Domain\Sudoku\Grid;

/**
 * Interfaccia per i solver di Sudoku
 */
interface SolverInterface
{
    /**
     * Risolve completamente una griglia Sudoku
     * 
     * @return array{grid: Grid|null, techniques: array<string>, steps: array<array>}
     */
    public function solve(Grid $grid): array;

    /**
     * Esegue un singolo passo di risoluzione
     * 
     * @return array{grid: Grid|null, technique: string|null, step: array|null}
     */
    public function solveStep(Grid $grid): array;

    /**
     * Trova tutti i possibili suggerimenti per il prossimo passo
     * 
     * @return array<array{technique: string, row: int, col: int, value?: int, candidates?: array<int>}>
     */
    public function getHints(Grid $grid): array;

    /**
     * Verifica se una griglia è risolvibile con tecniche logiche
     */
    public function isSolvableLogically(Grid $grid): bool;

    /**
     * Ottiene la lista delle tecniche supportate
     * 
     * @return array<string>
     */
    public function getSupportedTechniques(): array;
}
