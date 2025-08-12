<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\ValueObjects;

use App\Domain\Sudoku\Exceptions\InvalidMoveException;

/**
 * Rappresenta una singola mossa dell'utente in una partita di Sudoku.
 * 
 * Una mossa può essere:
 * - Inserimento di un valore in una cella
 * - Cancellazione di un valore da una cella
 * - Aggiunta/rimozione di candidati (pencil marks)
 */
final readonly class Move
{
    public const TYPE_SET_VALUE = 'set_value';
    public const TYPE_CLEAR_VALUE = 'clear_value';
    public const TYPE_SET_CANDIDATES = 'set_candidates';

    private function __construct(
        public string $type,
        public int $row,
        public int $col,
        public ?int $value,
        public ?CandidateSet $candidates,
        public int $timestamp
    ) {
        if ($row < 0 || $row > 8) {
            throw new InvalidMoveException("Row must be between 0 and 8, got {$row}");
        }
        
        if ($col < 0 || $col > 8) {
            throw new InvalidMoveException("Column must be between 0 and 8, got {$col}");
        }

        if (!in_array($type, [self::TYPE_SET_VALUE, self::TYPE_CLEAR_VALUE, self::TYPE_SET_CANDIDATES])) {
            throw new InvalidMoveException("Invalid move type: {$type}");
        }

        // Validazione specifica per tipo
        match ($type) {
            self::TYPE_SET_VALUE => $this->validateSetValue($value),
            self::TYPE_CLEAR_VALUE => $this->validateClearValue($value),
            self::TYPE_SET_CANDIDATES => $this->validateSetCandidates($candidates),
        };
    }

    /**
     * Crea una mossa di inserimento valore
     */
    public static function setValue(int $row, int $col, int $value, ?int $timestamp = null): self
    {
        return new self(
            type: self::TYPE_SET_VALUE,
            row: $row,
            col: $col,
            value: $value,
            candidates: null,
            timestamp: $timestamp ?? time()
        );
    }

    /**
     * Crea una mossa di cancellazione valore
     */
    public static function clearValue(int $row, int $col, ?int $timestamp = null): self
    {
        return new self(
            type: self::TYPE_CLEAR_VALUE,
            row: $row,
            col: $col,
            value: null,
            candidates: null,
            timestamp: $timestamp ?? time()
        );
    }

    /**
     * Crea una mossa di impostazione candidati
     */
    public static function setCandidates(int $row, int $col, CandidateSet $candidates, ?int $timestamp = null): self
    {
        return new self(
            type: self::TYPE_SET_CANDIDATES,
            row: $row,
            col: $col,
            value: null,
            candidates: $candidates,
            timestamp: $timestamp ?? time()
        );
    }

    /**
     * Verifica se è una mossa di inserimento valore
     */
    public function isSetValue(): bool
    {
        return $this->type === self::TYPE_SET_VALUE;
    }

    /**
     * Verifica se è una mossa di cancellazione
     */
    public function isClearValue(): bool
    {
        return $this->type === self::TYPE_CLEAR_VALUE;
    }

    /**
     * Verifica se è una mossa sui candidati
     */
    public function isSetCandidates(): bool
    {
        return $this->type === self::TYPE_SET_CANDIDATES;
    }

    /**
     * Ottiene la posizione come tupla
     */
    public function getPosition(): array
    {
        return [$this->row, $this->col];
    }

    /**
     * Verifica se la mossa riguarda la stessa cella di un'altra mossa
     */
    public function samePosition(self $other): bool
    {
        return $this->row === $other->row && $this->col === $other->col;
    }

    /**
     * Converte in array per serializzazione
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'row' => $this->row,
            'col' => $this->col,
            'value' => $this->value,
            'candidates' => $this->candidates?->toString(),
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Crea una mossa da array
     */
    public static function fromArray(array $data): self
    {
        $candidates = null;
        if (isset($data['candidates']) && is_string($data['candidates'])) {
            $candidates = CandidateSet::fromString($data['candidates']);
        }

        return new self(
            type: $data['type'],
            row: $data['row'],
            col: $data['col'],
            value: $data['value'] ?? null,
            candidates: $candidates,
            timestamp: $data['timestamp']
        );
    }

    /**
     * Rappresentazione JSON per storage
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Crea una mossa da JSON
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new InvalidMoveException("Invalid JSON format");
        }

        return self::fromArray($data);
    }

    /**
     * Rappresentazione in stringa per debug
     */
    public function toString(): string
    {
        $pos = "({$this->row},{$this->col})";
        
        return match ($this->type) {
            self::TYPE_SET_VALUE => "{$pos}={$this->value}",
            self::TYPE_CLEAR_VALUE => "{$pos}=clear",
            self::TYPE_SET_CANDIDATES => "{$pos}=candidates[{$this->candidates->toString()}]",
        };
    }

    /**
     * Rappresentazione dettagliata per debug
     */
    public function toDetailedString(): string
    {
        $timeStr = date('H:i:s', $this->timestamp);
        return "[{$timeStr}] {$this->toString()}";
    }

    /**
     * Valida una mossa di inserimento valore
     */
    private function validateSetValue(?int $value): void
    {
        if ($value === null || $value < 1 || $value > 9) {
            throw new InvalidMoveException("Set value move requires a value between 1 and 9, got " . ($value ?? 'null'));
        }
    }

    /**
     * Valida una mossa di cancellazione
     */
    private function validateClearValue(?int $value): void
    {
        if ($value !== null) {
            throw new InvalidMoveException("Clear value move should not have a value");
        }
    }

    /**
     * Valida una mossa sui candidati
     */
    private function validateSetCandidates(?CandidateSet $candidates): void
    {
        if ($candidates === null) {
            throw new InvalidMoveException("Set candidates move requires candidates");
        }
    }
}
