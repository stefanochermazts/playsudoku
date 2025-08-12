<?php
declare(strict_types=1);

namespace App\Domain\Sudoku;

use App\Domain\Sudoku\Exceptions\InvalidGridException;
use App\Domain\Sudoku\ValueObjects\Cell;
use App\Domain\Sudoku\ValueObjects\CandidateSet;

/**
 * Rappresenta una griglia Sudoku 9x9.
 * 
 * La griglia è immutabile - ogni modifica ritorna una nuova istanza.
 */
final readonly class Grid
{
    /** @var array<int, array<int, Cell>> */
    private array $cells;

    /**
     * @param array<int, array<int, Cell>> $cells
     */
    private function __construct(array $cells)
    {
        if (count($cells) !== 9) {
            throw new InvalidGridException("Grid must have 9 rows");
        }

        foreach ($cells as $row => $rowCells) {
            if (count($rowCells) !== 9) {
                throw new InvalidGridException("Row {$row} must have 9 cells");
            }
        }

        $this->cells = $cells;
    }

    /**
     * Crea una griglia vuota
     */
    public static function empty(): self
    {
        $cells = [];
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cells[$row][$col] = Cell::empty($row, $col);
            }
        }

        return new self($cells);
    }

    /**
     * Crea una griglia da un array 2D di valori
     * 
     * @param array<int, array<int, int|null>> $values
     */
    public static function fromArray(array $values): self
    {
        if (count($values) !== 9) {
            throw new InvalidGridException("Grid must have 9 rows");
        }

        $cells = [];
        for ($row = 0; $row < 9; $row++) {
            if (!isset($values[$row]) || count($values[$row]) !== 9) {
                throw new InvalidGridException("Row {$row} must have 9 values");
            }

            for ($col = 0; $col < 9; $col++) {
                $value = $values[$row][$col];
                
                if ($value === null || $value === 0) {
                    $cells[$row][$col] = Cell::empty($row, $col);
                } else {
                    $cells[$row][$col] = Cell::given($row, $col, $value);
                }
            }
        }

        return new self($cells);
    }

    /**
     * Crea una griglia da una stringa di 81 caratteri
     * 
     * @param string $str Stringa di 81 caratteri (0 o . = vuoto, 1-9 = valore)
     */
    public static function fromString(string $str): self
    {
        if (strlen($str) !== 81) {
            throw new InvalidGridException("String must be 81 characters long");
        }

        $values = [];
        for ($i = 0; $i < 81; $i++) {
            $char = $str[$i];
            $row = intval($i / 9);
            $col = $i % 9;

            if ($char === '0' || $char === '.') {
                $values[$row][$col] = null;
            } elseif ($char >= '1' && $char <= '9') {
                $values[$row][$col] = (int) $char;
            } else {
                throw new InvalidGridException("Invalid character '{$char}' at position {$i}");
            }
        }

        return self::fromArray($values);
    }

    /**
     * Ottiene una cella specifica
     */
    public function getCell(int $row, int $col): Cell
    {
        if ($row < 0 || $row > 8 || $col < 0 || $col > 8) {
            throw new InvalidGridException("Invalid cell position ({$row}, {$col})");
        }

        return $this->cells[$row][$col];
    }

    /**
     * Imposta il valore di una cella
     */
    public function setCell(int $row, int $col, int $value): self
    {
        $newCells = $this->cells;
        $newCells[$row][$col] = $this->getCell($row, $col)->setValue($value);

        return new self($newCells);
    }

    /**
     * Cancella il valore di una cella
     */
    public function clearCell(int $row, int $col): self
    {
        $newCells = $this->cells;
        $newCells[$row][$col] = $this->getCell($row, $col)->clearValue();

        return new self($newCells);
    }

    /**
     * Aggiorna i candidati di una cella
     */
    public function updateCellCandidates(int $row, int $col, CandidateSet $candidates): self
    {
        $newCells = $this->cells;
        $newCells[$row][$col] = $this->getCell($row, $col)->withCandidates($candidates);

        return new self($newCells);
    }

    /**
     * Ottiene tutte le celle di una riga
     * 
     * @return array<int, Cell>
     */
    public function getRow(int $row): array
    {
        if ($row < 0 || $row > 8) {
            throw new InvalidGridException("Invalid row: {$row}");
        }

        return $this->cells[$row];
    }

    /**
     * Ottiene tutte le celle di una colonna
     * 
     * @return array<int, Cell>
     */
    public function getCol(int $col): array
    {
        if ($col < 0 || $col > 8) {
            throw new InvalidGridException("Invalid column: {$col}");
        }

        $colCells = [];
        for ($row = 0; $row < 9; $row++) {
            $colCells[$row] = $this->cells[$row][$col];
        }

        return $colCells;
    }

    /**
     * Ottiene tutte le celle di un box (quadrante 3x3)
     * 
     * @return array<int, Cell>
     */
    public function getBox(int $box): array
    {
        if ($box < 0 || $box > 8) {
            throw new InvalidGridException("Invalid box: {$box}");
        }

        $startRow = intval($box / 3) * 3;
        $startCol = ($box % 3) * 3;

        $boxCells = [];
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 3; $c++) {
                $boxCells[] = $this->cells[$startRow + $r][$startCol + $c];
            }
        }

        return $boxCells;
    }

    /**
     * Ottiene i peers di una cella (stessa riga, colonna o box)
     * 
     * @return array<string, array<int, Cell>>
     */
    public function getPeers(int $row, int $col): array
    {
        $cell = $this->getCell($row, $col);
        
        return [
            'row' => array_filter($this->getRow($row), fn($c) => $c->col !== $col),
            'col' => array_filter($this->getCol($col), fn($c) => $c->row !== $row),
            'box' => array_filter($this->getBox($cell->getBox()), 
                fn($c) => $c->row !== $row || $c->col !== $col)
        ];
    }

    /**
     * Verifica se la griglia è valida (nessun conflitto)
     */
    public function isValid(): bool
    {
        // Controlla righe
        for ($row = 0; $row < 9; $row++) {
            if (!$this->isGroupValid($this->getRow($row))) {
                return false;
            }
        }

        // Controlla colonne
        for ($col = 0; $col < 9; $col++) {
            if (!$this->isGroupValid($this->getCol($col))) {
                return false;
            }
        }

        // Controlla box
        for ($box = 0; $box < 9; $box++) {
            if (!$this->isGroupValid($this->getBox($box))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se la griglia è completa (tutte le celle hanno un valore)
     */
    public function isComplete(): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->getCell($row, $col)->isEmpty()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Verifica se la griglia è risolta (completa e valida)
     */
    public function isSolved(): bool
    {
        return $this->isComplete() && $this->isValid();
    }

    /**
     * Conta le celle vuote
     */
    public function countEmptyCells(): int
    {
        $count = 0;
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->getCell($row, $col)->isEmpty()) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Conta le celle given (valori iniziali)
     */
    public function countGivenCells(): int
    {
        $count = 0;
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->getCell($row, $col)->isGiven) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Converte la griglia in un array 2D
     * 
     * @return array<int, array<int, int|null>>
     */
    public function toArray(): array
    {
        $result = [];
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $result[$row][$col] = $this->getCell($row, $col)->value;
            }
        }

        return $result;
    }

    /**
     * Converte la griglia in una stringa di 81 caratteri
     */
    public function toString(): string
    {
        $result = '';
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $this->getCell($row, $col);
                $result .= $cell->hasValue() ? (string) $cell->value : '.';
            }
        }

        return $result;
    }

    /**
     * Rappresentazione dettagliata per debug
     */
    public function toDetailedString(): string
    {
        $result = "\n";
        for ($row = 0; $row < 9; $row++) {
            if ($row % 3 === 0 && $row > 0) {
                $result .= "------+-------+------\n";
            }

            for ($col = 0; $col < 9; $col++) {
                if ($col % 3 === 0 && $col > 0) {
                    $result .= "| ";
                }

                $cell = $this->getCell($row, $col);
                $result .= $cell->toString() . " ";
            }
            $result .= "\n";
        }

        return $result;
    }

    /**
     * Verifica se un gruppo di celle è valido (nessun duplicato)
     * 
     * @param array<int, Cell> $cells
     */
    private function isGroupValid(array $cells): bool
    {
        $seen = [];
        
        foreach ($cells as $cell) {
            if ($cell->hasValue()) {
                $value = $cell->value;
                if (isset($seen[$value])) {
                    return false; // Duplicato trovato
                }
                $seen[$value] = true;
            }
        }

        return true;
    }
}
