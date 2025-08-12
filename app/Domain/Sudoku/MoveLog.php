<?php
declare(strict_types=1);

namespace App\Domain\Sudoku;

use App\Domain\Sudoku\Exceptions\InvalidMoveException;
use App\Domain\Sudoku\ValueObjects\Move;

/**
 * Log immutabile di tutte le mosse effettuate in una partita di Sudoku.
 * 
 * Consente di:
 * - Tracciare la cronologia completa delle mosse
 * - Calcolare statistiche (tempo, errori, hints)
 * - Implementare undo/redo
 * - Replay della partita
 */
final readonly class MoveLog
{
    /** @var array<int, Move> */
    private array $moves;

    /**
     * @param array<int, Move> $moves
     */
    private function __construct(array $moves)
    {
        // Valida che gli indici siano consecutivi partendo da 0
        $expectedIndex = 0;
        foreach ($moves as $index => $move) {
            if ($index !== $expectedIndex) {
                throw new InvalidMoveException("Invalid move index: expected {$expectedIndex}, got {$index}");
            }
            $expectedIndex++;
        }

        $this->moves = $moves;
    }

    /**
     * Crea un log vuoto
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Crea un log da un array di mosse
     * 
     * @param array<int, Move> $moves
     */
    public static function fromMoves(array $moves): self
    {
        return new self(array_values($moves)); // Re-index to ensure consecutive indices
    }

    /**
     * Aggiunge una mossa al log
     */
    public function addMove(Move $move): self
    {
        $newMoves = $this->moves;
        $newMoves[] = $move;

        return new self($newMoves);
    }

    /**
     * Rimuove l'ultima mossa (undo)
     */
    public function undoLastMove(): self
    {
        if ($this->isEmpty()) {
            throw new InvalidMoveException("Cannot undo: no moves in log");
        }

        $newMoves = $this->moves;
        array_pop($newMoves);

        return new self($newMoves);
    }

    /**
     * Rimuove le ultime N mosse
     */
    public function undoMoves(int $count): self
    {
        if ($count < 0) {
            throw new InvalidMoveException("Count must be non-negative, got {$count}");
        }

        if ($count > $this->count()) {
            throw new InvalidMoveException("Cannot undo {$count} moves: only {$this->count()} moves in log");
        }

        $newMoves = array_slice($this->moves, 0, -$count);

        return new self($newMoves);
    }

    /**
     * Tronca il log fino a un certo indice (mantiene mosse 0 -> index-1)
     */
    public function truncateAt(int $index): self
    {
        if ($index < 0 || $index > $this->count()) {
            throw new InvalidMoveException("Invalid truncate index: {$index}");
        }

        $newMoves = array_slice($this->moves, 0, $index);

        return new self($newMoves);
    }

    /**
     * Ottiene una mossa specifica per indice
     */
    public function getMove(int $index): Move
    {
        if (!isset($this->moves[$index])) {
            throw new InvalidMoveException("Move index {$index} not found");
        }

        return $this->moves[$index];
    }

    /**
     * Ottiene l'ultima mossa
     */
    public function getLastMove(): ?Move
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->moves[count($this->moves) - 1];
    }

    /**
     * Ottiene tutte le mosse
     * 
     * @return array<int, Move>
     */
    public function getAllMoves(): array
    {
        return $this->moves;
    }

    /**
     * Ottiene le mosse in un range
     * 
     * @return array<int, Move>
     */
    public function getMovesRange(int $start, int $length): array
    {
        if ($start < 0 || $start >= $this->count()) {
            throw new InvalidMoveException("Invalid start index: {$start}");
        }

        return array_slice($this->moves, $start, $length, true);
    }

    /**
     * Filtra le mosse per tipo
     * 
     * @return array<int, Move>
     */
    public function filterByType(string $type): array
    {
        return array_filter($this->moves, fn(Move $move) => $move->type === $type);
    }

    /**
     * Filtra le mosse per posizione
     * 
     * @return array<int, Move>
     */
    public function filterByPosition(int $row, int $col): array
    {
        return array_filter($this->moves, 
            fn(Move $move) => $move->row === $row && $move->col === $col);
    }

    /**
     * Verifica se il log è vuoto
     */
    public function isEmpty(): bool
    {
        return count($this->moves) === 0;
    }

    /**
     * Conta il numero di mosse
     */
    public function count(): int
    {
        return count($this->moves);
    }

    /**
     * Conta mosse per tipo
     */
    public function countByType(string $type): int
    {
        return count($this->filterByType($type));
    }

    /**
     * Calcola il tempo totale della partita (dall'inizio alla fine)
     */
    public function getTotalDuration(): int
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $firstMove = $this->moves[0];
        $lastMove = $this->moves[count($this->moves) - 1];

        return $lastMove->timestamp - $firstMove->timestamp;
    }

    /**
     * Calcola statistiche della partita
     */
    public function getStatistics(): array
    {
        return [
            'total_moves' => $this->count(),
            'set_value_moves' => $this->countByType(Move::TYPE_SET_VALUE),
            'clear_value_moves' => $this->countByType(Move::TYPE_CLEAR_VALUE),
            'candidate_moves' => $this->countByType(Move::TYPE_SET_CANDIDATES),
            'duration_seconds' => $this->getTotalDuration(),
            'start_time' => $this->isEmpty() ? null : $this->moves[0]->timestamp,
            'end_time' => $this->isEmpty() ? null : $this->getLastMove()->timestamp,
        ];
    }

    /**
     * Conta gli errori (mosse che inseriscono valori poi cancellati)
     */
    public function countErrors(): int
    {
        $errors = 0;
        $cellHistory = [];

        foreach ($this->moves as $move) {
            $key = "{$move->row},{$move->col}";
            
            if ($move->isSetValue()) {
                $cellHistory[$key][] = $move;
            } elseif ($move->isClearValue()) {
                // Se c'era un valore settato prima, conta come errore
                if (isset($cellHistory[$key]) && count($cellHistory[$key]) > 0) {
                    $errors++;
                }
                $cellHistory[$key][] = $move;
            }
        }

        return $errors;
    }

    /**
     * Verifica se ci sono state modifiche a una cella specifica
     */
    public function hasCellBeenModified(int $row, int $col): bool
    {
        return count($this->filterByPosition($row, $col)) > 0;
    }

    /**
     * Ottiene l'ultima mossa per una cella specifica
     */
    public function getLastMoveForCell(int $row, int $col): ?Move
    {
        $cellMoves = $this->filterByPosition($row, $col);
        
        if (empty($cellMoves)) {
            return null;
        }

        return end($cellMoves);
    }

    /**
     * Converte in array per serializzazione
     */
    public function toArray(): array
    {
        return [
            'moves' => array_map(fn(Move $move) => $move->toArray(), $this->moves),
            'statistics' => $this->getStatistics(),
        ];
    }

    /**
     * Crea un log da array
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['moves']) || !is_array($data['moves'])) {
            throw new InvalidMoveException("Invalid log data: missing moves array");
        }

        $moves = array_map(fn(array $moveData) => Move::fromArray($moveData), $data['moves']);

        return new self($moves);
    }

    /**
     * Rappresentazione JSON per storage
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Crea un log da JSON
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
        if ($this->isEmpty()) {
            return "Empty move log";
        }

        $movesStr = array_map(fn(Move $move) => $move->toString(), $this->moves);
        return "Moves: " . implode(', ', $movesStr);
    }

    /**
     * Rappresentazione dettagliata per debug
     */
    public function toDetailedString(): string
    {
        if ($this->isEmpty()) {
            return "Empty move log";
        }

        $result = "Move Log ({$this->count()} moves):\n";
        foreach ($this->moves as $index => $move) {
            $result .= sprintf("%3d: %s\n", $index, $move->toDetailedString());
        }

        $stats = $this->getStatistics();
        $result .= "\nStatistics:\n";
        $result .= "- Total moves: {$stats['total_moves']}\n";
        $result .= "- Duration: {$stats['duration_seconds']} seconds\n";
        $result .= "- Errors: {$this->countErrors()}\n";

        return $result;
    }
}
