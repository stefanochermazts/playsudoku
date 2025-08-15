<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\OptimizedQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modello per i tentativi di sfida
 * 
 * @property int $id
 * @property int $challenge_id
 * @property int $user_id
 * @property int|null $duration_ms
 * @property int $errors_count
 * @property int $hints_used
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property bool $valid
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ChallengeAttempt extends Model
{
    use HasFactory, OptimizedQueries;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'started_at',
        'last_activity_at',
        'pause_started_at',
        'paused_ms_total',
        'pauses_count',
        'duration_ms',
        'errors_count',
        'hints_used',
        'completed_at',
        'valid',
        'current_state',
        'final_state',
        'move_validation_passed',
        'validated_at',
        'validation_notes',
        'flagged_for_review',
        'reviewed_at',
        'reviewed_by',
        'admin_notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'pause_started_at' => 'datetime',
        'paused_ms_total' => 'integer',
        'pauses_count' => 'integer',
        'duration_ms' => 'integer',
        'errors_count' => 'integer',
        'hints_used' => 'integer',
        'valid' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'current_state' => 'array',
        'final_state' => 'array',
        'move_validation_passed' => 'boolean',
        'validated_at' => 'datetime',
        'flagged_for_review' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Relazione: un tentativo appartiene a una sfida
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Relazione: un tentativo appartiene a un utente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione: un tentativo può avere molte mosse (per replay)
     */
    public function moves(): HasMany
    {
        return $this->hasMany(AttemptMove::class, 'attempt_id')->orderBy('move_index');
    }

    /**
     * Relazione: admin che ha revisionato il tentativo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope: tentativi completati
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope: tentativi validi
     */
    public function scopeValid($query)
    {
        return $query->where('valid', true);
    }

    /**
     * Scope: ordinati per miglior tempo penalizzato (per leaderboard)
     */
    public function scopeByBestTime($query)
    {
        return $query->whereNotNull('completed_at')
            ->where('valid', true)
            ->where(function($q) {
                // Include i tentativi non ancora validati O quelli che hanno passato la validazione
                $q->whereNull('move_validation_passed')
                  ->orWhere('move_validation_passed', true);
            })
            ->where('flagged_for_review', false)
            ->orderByRaw('(duration_ms + (errors_count * 3000))')
            ->orderBy('hints_used')
            ->orderBy('completed_at');
    }

    /**
     * Scope: tentativi sospetti che necessitano revisione
     */
    public function scopeSuspicious($query)
    {
        return $query->where(function($q) {
            $q->where('move_validation_passed', false)
              ->orWhere('flagged_for_review', true);
        });
    }

    /**
     * Scope: tentativi validati e puliti
     */
    public function scopeValidated($query)
    {
        return $query->where('valid', true)
            ->where('move_validation_passed', true)
            ->where('flagged_for_review', false);
    }

    /**
     * Verifica se il tentativo è completato
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Calcola il punteggio in base ai criteri di tie-break
     * (meno errori → timestamp più antico → meno hint)
     */
    public function getScore(): array
    {
        return [
            'duration_ms' => $this->duration_ms,
            'errors_count' => $this->errors_count,
            'hints_used' => $this->hints_used,
            'completed_at' => $this->completed_at?->timestamp ?? null,
        ];
    }

    /**
     * Calcola il tempo penalizzato (tempo + 3 secondi per errore)
     */
    public function getPenalizedTime(): int
    {
        if (!$this->duration_ms) {
            return 0;
        }

        // 3 secondi di penalizzazione per errore (3000ms)
        return $this->duration_ms + ($this->errors_count * 3000);
    }

    /**
     * Calcola la durata formattata
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration_ms) {
            return '--:--.--';
        }

        $totalMs = (int) $this->duration_ms;
        $minutes = intdiv($totalMs, 60_000);
        $seconds = intdiv($totalMs % 60_000, 1000);
        $centis = intdiv($totalMs % 1000, 10);

        return sprintf('%02d:%02d.%02d', $minutes, $seconds, $centis);
    }

    /**
     * Calcola la durata penalizzata formattata
     */
    public function getFormattedPenalizedDuration(): string
    {
        $penalizedMs = $this->getPenalizedTime();
        
        if (!$penalizedMs) {
            return '--:--.--';
        }

        $totalMs = (int) $penalizedMs;
        $minutes = intdiv($totalMs, 60_000);
        $seconds = intdiv($totalMs % 60_000, 1000);
        $centis = intdiv($totalMs % 1000, 10);

        return sprintf('%02d:%02d.%02d', $minutes, $seconds, $centis);
    }
}
