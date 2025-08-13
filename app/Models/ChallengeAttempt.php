<?php
declare(strict_types=1);

namespace App\Models;

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
    use HasFactory;

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
     * Scope: ordinati per miglior tempo (per leaderboard)
     */
    public function scopeByBestTime($query)
    {
        return $query->whereNotNull('completed_at')
            ->where('valid', true)
            ->orderBy('duration_ms')
            ->orderBy('errors_count')
            ->orderBy('hints_used')
            ->orderBy('completed_at');
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
     * Calcola la durata formattata
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration_ms) {
            return '--:--';
        }

        $totalSeconds = intval($this->duration_ms / 1000);
        $minutes = intval($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
