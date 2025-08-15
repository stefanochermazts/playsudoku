<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\Contracts\SolverInterface;
use Livewire\Component;

/**
 * Componente per analizzare puzzle importati e mostrare le tecniche utilizzate
 */
class PuzzleAnalyzer extends Component
{
    public array $inputGrid = [];
    public array $solverReport = [];
    public string $inputMethod = 'manual'; // 'manual' o 'json'
    public string $jsonInput = '';
    public string $errorMessage = '';
    public bool $isAnalyzing = false;
    public bool $hasResults = false;

    public function mount(): void
    {
        // Inizializza griglia vuota 9x9
        $this->inputGrid = array_fill(0, 9, array_fill(0, 9, null));
    }

    /**
     * Aggiorna un valore nella griglia di input
     */
    public function updateCell(int $row, int $col, ?int $value): void
    {
        if ($value === 0) {
            $value = null;
        }
        
        $this->inputGrid[$row][$col] = $value;
        $this->clearResults();
    }

    /**
     * Cambia metodo di input
     */
    public function setInputMethod(string $method): void
    {
        $this->inputMethod = $method;
        $this->clearResults();
        $this->errorMessage = '';
    }

    /**
     * Importa puzzle da JSON
     */
    public function importFromJson(): void
    {
        try {
            $data = json_decode($this->jsonInput, true);
            
            if (!is_array($data) || count($data) !== 9) {
                throw new \Exception(__('app.analyzer.error_json_format'));
            }
            
            foreach ($data as $rowIndex => $row) {
                if (!is_array($row) || count($row) !== 9) {
                    throw new \Exception(__('app.analyzer.error_json_format'));
                }
                
                foreach ($row as $colIndex => $cell) {
                    if ($cell !== null && (!is_int($cell) || $cell < 1 || $cell > 9)) {
                        throw new \Exception(__('app.analyzer.error_json_format'));
                    }
                }
            }
            
            $this->inputGrid = $data;
            $this->errorMessage = '';
            $this->clearResults();
            
        } catch (\Exception $e) {
            $this->errorMessage = __('app.analyzer.error_json_parsing', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Analizza il puzzle con il solver
     */
    public function analyzePuzzle(): void
    {
        $this->isAnalyzing = true;
        $this->errorMessage = '';
        
        try {
            // Verifica che ci siano alcuni valori iniziali
            $givenCount = 0;
            foreach ($this->inputGrid as $row) {
                foreach ($row as $cell) {
                    if ($cell !== null) {
                        $givenCount++;
                    }
                }
            }
            
            if ($givenCount < 17) {
                throw new \Exception(__('app.analyzer.error_empty_grid'));
            }
            
            // Crea oggetto Grid
            $grid = Grid::fromArray($this->inputGrid);
            
            // Usa il solver
            /** @var SolverInterface $solver */
            $solver = app(SolverInterface::class);
            
            $result = $solver->solve($grid);
            
            if ($result['grid'] === null) {
                throw new \Exception(__('app.analyzer.error_analysis', ['error' => 'Puzzle not solvable with available techniques']));
            }
            
            $this->solverReport = [
                'givenCount' => $givenCount,
                'techniques' => $result['techniques'],
                'steps' => $result['steps'],
                'totalSteps' => count($result['steps']),
                'uniqueTechniques' => array_unique($result['techniques']),
                'techniqueCounts' => array_count_values($result['techniques']),
                'finalGrid' => $result['grid']->toArray(),
                'isComplete' => $result['grid']->isComplete(),
            ];
            
            $this->hasResults = true;
            
        } catch (\Exception $e) {
            $this->errorMessage = __('app.analyzer.error_analysis', ['error' => $e->getMessage()]);
        } finally {
            $this->isAnalyzing = false;
        }
    }

    /**
     * Carica puzzle di esempio
     */
    public function loadSamplePuzzle(): void
    {
        // Puzzle classico ben bilanciato - facile da risolvere per testare l'analyzer
        // Dovrebbe mostrare tecniche standard: Naked Singles, Hidden Singles, Pointing/Claiming
        $this->inputGrid = [
            [5, 3, null, null, 7, null, null, null, null],
            [6, null, null, 1, 9, 5, null, null, null],
            [null, 9, 8, null, null, null, null, 6, null],
            [8, null, null, null, 6, null, null, null, 3],
            [4, null, null, 8, null, 3, null, null, 1],
            [7, null, null, null, 2, null, null, null, 6],
            [null, 6, null, null, null, null, 2, 8, null],
            [null, null, null, 4, 1, 9, null, null, 5],
            [null, null, null, null, 8, null, null, 7, 9]
        ];
        
        $this->clearResults();
        $this->errorMessage = '';
        
        // Notifica che il puzzle difficile è stato caricato
        session()->flash('success', __('app.analyzer.sample_puzzle_loaded'));
    }

    /**
     * Pulisce i risultati dell'analisi
     */
    public function clearResults(): void
    {
        $this->hasResults = false;
        $this->solverReport = [];
    }

    /**
     * Pulisce completamente la griglia
     */
    public function clearGrid(): void
    {
        $this->inputGrid = array_fill(0, 9, array_fill(0, 9, null));
        $this->clearResults();
        $this->errorMessage = '';
    }

    /**
     * Ottiene il nome tradotto di una tecnica
     */
    public function getTechniqueName(string $technique): string
    {
        $translationKey = 'app.hints.techniques.' . $technique;
        $translated = __($translationKey);
        
        // Se la traduzione non esiste, usa un fallback
        if ($translated === $translationKey) {
            return ucfirst(str_replace('_', ' ', $technique));
        }
        
        return $translated;
    }

    public function render()
    {
        return view('livewire.puzzle-analyzer');
    }
}


