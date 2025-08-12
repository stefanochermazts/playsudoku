<?php
declare(strict_types=1);

namespace App\Models;

use App\Domain\Sudoku\ValueObjects\Move;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modello per le mosse di un tentativo (per replay)
 * 
 * @property int $id
 * @property int $attempt_id
 * @property int $move_index
 * @property array $payload_json
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AttemptMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'move_index',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione: una mossa appartiene a un tentativo
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ChallengeAttempt::class, 'attempt_id');
    }

    /**
     * Converte il payload in un oggetto Move del dominio
     */
    public function toDomainMove(): Move
    {
        $payload = $this->payload_json;
        
        return match ($payload['type']) {
            'set_value' => Move::setValue(
                $payload['row'], 
                $payload['col'], 
                $payload['value'], 
                $payload['timestamp'] ?? time()
            ),
            'clear_value' => Move::clearValue(
                $payload['row'], 
                $payload['col'], 
                $payload['timestamp'] ?? time()
            ),
            'set_candidates' => Move::setCandidates(
                $payload['row'], 
                $payload['col'], 
                \App\Domain\Sudoku\ValueObjects\CandidateSet::from($payload['candidates']), 
                $payload['timestamp'] ?? time()
            ),
            default => throw new \InvalidArgumentException("Invalid move type: {$payload['type']}")
        };
    }

    /**
     * Crea un AttemptMove da un oggetto Move del dominio
     */
    public static function fromDomainMove(int $attemptId, int $moveIndex, Move $move): self
    {
        $payload = [
            'type' => $move->type,
            'row' => $move->row,
            'col' => $move->col,
            'timestamp' => $move->timestamp,
        ];

        if ($move->value !== null) {
            $payload['value'] = $move->value;
        }

        if ($move->candidates !== null) {
            $payload['candidates'] = $move->candidates->toArray();
        }

        return new self([
            'attempt_id' => $attemptId,
            'move_index' => $moveIndex,
            'payload_json' => $payload,
        ]);
    }

    /**
     * Scope: ordina per indice mossa (per replay sequenziale)
     */
    public function scopeOrderedByIndex($query)
    {
        return $query->orderBy('move_index');
    }
}
