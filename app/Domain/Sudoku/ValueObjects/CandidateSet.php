<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\ValueObjects;

use App\Domain\Sudoku\Exceptions\InvalidCellValueException;

/**
 * Set immutabile di candidati possibili per una cella Sudoku.
 * 
 * Internamente usa un bitmask per efficienza:
 * - Bit 0 = candidato 1
 * - Bit 1 = candidato 2
 * - ...
 * - Bit 8 = candidato 9
 */
final readonly class CandidateSet
{
    private function __construct(
        private int $mask
    ) {
        if ($mask < 0 || $mask > 511) { // 2^9 - 1 = 511
            throw new InvalidCellValueException("Invalid candidate mask: {$mask}");
        }
    }

    /**
     * Crea un set vuoto di candidati
     */
    public static function empty(): self
    {
        return new self(0);
    }

    /**
     * Crea un set con tutti i candidati possibili (1-9)
     */
    public static function all(): self
    {
        return new self(511); // 0b111111111 = tutti i 9 bit attivi
    }

    /**
     * Crea un set da un array di valori
     */
    public static function from(array $values): self
    {
        $mask = 0;
        foreach ($values as $value) {
            if ($value < 1 || $value > 9) {
                throw new InvalidCellValueException("Invalid candidate value: {$value}");
            }
            $mask |= (1 << ($value - 1));
        }
        
        return new self($mask);
    }

    /**
     * Crea un set da un singolo valore
     */
    public static function single(int $value): self
    {
        return self::from([$value]);
    }

    /**
     * Crea un set da una stringa di cifre (es: "123" -> candidati 1, 2, 3)
     */
    public static function fromString(string $str): self
    {
        $values = [];
        for ($i = 0; $i < strlen($str); $i++) {
            $digit = (int) $str[$i];
            if ($digit >= 1 && $digit <= 9) {
                $values[] = $digit;
            }
        }
        
        return self::from($values);
    }

    /**
     * Verifica se il set contiene un candidato
     */
    public function contains(int $value): bool
    {
        if ($value < 1 || $value > 9) {
            return false;
        }
        
        return ($this->mask & (1 << ($value - 1))) !== 0;
    }

    /**
     * Aggiunge un candidato al set
     */
    public function add(int $value): self
    {
        if ($value < 1 || $value > 9) {
            throw new InvalidCellValueException("Invalid candidate value: {$value}");
        }
        
        return new self($this->mask | (1 << ($value - 1)));
    }

    /**
     * Rimuove un candidato dal set
     */
    public function remove(int $value): self
    {
        if ($value < 1 || $value > 9) {
            return $this; // Valore non valido, nessuna modifica
        }
        
        return new self($this->mask & ~(1 << ($value - 1)));
    }

    /**
     * Rimuove più candidati dal set
     */
    public function removeAll(array $values): self
    {
        $result = $this;
        foreach ($values as $value) {
            $result = $result->remove($value);
        }
        
        return $result;
    }

    /**
     * Intersezione con un altro set
     */
    public function intersect(self $other): self
    {
        return new self($this->mask & $other->mask);
    }

    /**
     * Unione con un altro set
     */
    public function union(self $other): self
    {
        return new self($this->mask | $other->mask);
    }

    /**
     * Differenza con un altro set (elementi in questo set ma non nell'altro)
     */
    public function difference(self $other): self
    {
        return new self($this->mask & ~$other->mask);
    }

    /**
     * Verifica se il set è vuoto
     */
    public function isEmpty(): bool
    {
        return $this->mask === 0;
    }

    /**
     * Verifica se il set contiene un solo candidato
     */
    public function isSingle(): bool
    {
        return $this->count() === 1;
    }

    /**
     * Conta il numero di candidati nel set
     */
    public function count(): int
    {
        return substr_count(decbin($this->mask), '1');
    }

    /**
     * Ottiene il primo (unico) candidato se il set ne contiene solo uno
     */
    public function getSingle(): ?int
    {
        if (!$this->isSingle()) {
            return null;
        }

        for ($i = 1; $i <= 9; $i++) {
            if ($this->contains($i)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Converte il set in un array di valori
     */
    public function toArray(): array
    {
        $values = [];
        for ($i = 1; $i <= 9; $i++) {
            if ($this->contains($i)) {
                $values[] = $i;
            }
        }
        
        return $values;
    }

    /**
     * Rappresentazione in stringa per debug (es: "123" per candidati 1, 2, 3)
     */
    public function toString(): string
    {
        return implode('', $this->toArray());
    }

    /**
     * Rappresentazione dettagliata per debug
     */
    public function toDetailedString(): string
    {
        if ($this->isEmpty()) {
            return '{}';
        }

        return '{' . $this->toString() . '}';
    }

    /**
     * Verifica uguaglianza con un altro set
     */
    public function equals(self $other): bool
    {
        return $this->mask === $other->mask;
    }

    /**
     * Ottiene la rappresentazione binaria interna (per debug)
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * Verifica se questo set è un sottoinsieme dell'altro
     */
    public function isSubsetOf(self $other): bool
    {
        return ($this->mask & $other->mask) === $this->mask;
    }

    /**
     * Verifica se questo set è un superinsieme dell'altro
     */
    public function isSupersetOf(self $other): bool
    {
        return $other->isSubsetOf($this);
    }
}
