<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\ValueObjects;

use App\Domain\Sudoku\Exceptions\InvalidCellValueException;

/**
 * Rappresenta una singola cella di una griglia Sudoku.
 * 
 * Una cella può contenere:
 * - Un valore definitivo (1-9)
 * - Nessun valore (vuota)
 * - Un set di candidati possibili
 */
final readonly class Cell
{
    private function __construct(
        public int $row,
        public int $col,
        public ?int $value,
        public CandidateSet $candidates,
        public bool $isGiven = false
    ) {
        if ($row < 0 || $row > 8) {
            throw new InvalidCellValueException("Row must be between 0 and 8, got {$row}");
        }
        
        if ($col < 0 || $col > 8) {
            throw new InvalidCellValueException("Column must be between 0 and 8, got {$col}");
        }
        
        if ($value !== null && ($value < 1 || $value > 9)) {
            throw new InvalidCellValueException("Cell value must be between 1 and 9, got {$value}");
        }
    }

    /**
     * Crea una cella vuota con tutti i candidati possibili
     */
    public static function empty(int $row, int $col): self
    {
        return new self(
            row: $row,
            col: $col,
            value: null,
            candidates: CandidateSet::all(),
            isGiven: false
        );
    }

    /**
     * Crea una cella con un valore definitivo
     */
    public static function withValue(int $row, int $col, int $value, bool $isGiven = false): self
    {
        return new self(
            row: $row,
            col: $col,
            value: $value,
            candidates: CandidateSet::empty(),
            isGiven: $isGiven
        );
    }

    /**
     * Crea una cella given (valore iniziale del puzzle)
     */
    public static function given(int $row, int $col, int $value): self
    {
        return self::withValue($row, $col, $value, true);
    }

    /**
     * Verifica se la cella è vuota
     */
    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    /**
     * Verifica se la cella ha un valore definitivo
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * Imposta un valore nella cella
     */
    public function setValue(int $value): self
    {
        if ($this->isGiven) {
            throw new InvalidCellValueException("Cannot modify a given cell");
        }

        return new self(
            row: $this->row,
            col: $this->col,
            value: $value,
            candidates: CandidateSet::empty(),
            isGiven: false
        );
    }

    /**
     * Rimuove il valore dalla cella
     */
    public function clearValue(): self
    {
        if ($this->isGiven) {
            throw new InvalidCellValueException("Cannot modify a given cell");
        }

        return new self(
            row: $this->row,
            col: $this->col,
            value: null,
            candidates: CandidateSet::all(),
            isGiven: false
        );
    }

    /**
     * Aggiorna i candidati della cella
     */
    public function withCandidates(CandidateSet $candidates): self
    {
        if ($this->hasValue()) {
            return $this; // Le celle con valore non hanno candidati
        }

        return new self(
            row: $this->row,
            col: $this->col,
            value: $this->value,
            candidates: $candidates,
            isGiven: $this->isGiven
        );
    }

    /**
     * Rimuove un candidato dalla cella
     */
    public function removeCandidate(int $candidate): self
    {
        return $this->withCandidates(
            $this->candidates->remove($candidate)
        );
    }

    /**
     * Verifica se la cella può contenere un determinato valore
     */
    public function canBe(int $value): bool
    {
        if ($this->hasValue()) {
            return $this->value === $value;
        }

        return $this->candidates->contains($value);
    }

    /**
     * Ottiene il box (quadrante 3x3) di appartenenza
     */
    public function getBox(): int
    {
        return (int) (floor($this->row / 3) * 3 + floor($this->col / 3));
    }

    /**
     * Rappresentazione in stringa per debug
     */
    public function toString(): string
    {
        if ($this->hasValue()) {
            return (string) $this->value;
        }

        if ($this->candidates->count() === 0) {
            return '!'; // Cella in stato invalido
        }

        return '.'; // Cella vuota
    }

    /**
     * Rappresentazione completa per debug
     */
    public function toDetailedString(): string
    {
        $base = "({$this->row},{$this->col})";
        
        if ($this->hasValue()) {
            $flag = $this->isGiven ? 'G' : 'V';
            return "{$base}={$this->value}[{$flag}]";
        }

        $candidatesStr = $this->candidates->toString();
        return "{$base}={{{$candidatesStr}}}";
    }
}
