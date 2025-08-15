<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modello per le sfide Sudoku
 * 
 * @property int $id
 * @property int $puzzle_id
 * @property string $type
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property string $visibility
 * @property string $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'puzzle_id',
        'type',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'visibility',
        'status',
        'created_by',
        'settings',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Relazione: una sfida appartiene a un puzzle
     */
    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }

    /**
     * Relazione: una sfida appartiene a un creatore (utente)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relazione: una sfida può avere molti tentativi
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class);
    }

    /**
     * Relazione: tentativi validi ordinati per tempo
     */
    public function validAttempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class)
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->orderBy('duration_ms');
    }

    /**
     * Scope: sfide attive
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    /**
     * Scope: filtra per tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: sfide pubbliche
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Verifica se la sfida è attiva
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->starts_at <= now() 
            && $this->ends_at > now();
    }

    /**
     * Verifica se l'utente può partecipare alla sfida
     */
    public function canUserParticipate(User $user): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Verifica se l'utente ha già completato la sfida
        return !$this->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    /**
     * Ottiene la classifica della sfida con criteri di tie-break
     * 
     * @param int $limit Numero massimo di risultati (default: nessun limite)
     * @param int $offset Offset per paginazione (default: 0)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLeaderboard(int $limit = null, int $offset = 0): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->attempts()
            ->with('user') // Eager load user per evitare N+1 queries
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            // Ordina secondo i criteri di tie-break:
            // 1. Tempo di completamento (duration_ms)
            // 2. Meno errori
            // 3. Timestamp di completamento più antico
            // 4. Meno hint usati
            ->orderBy('duration_ms', 'asc')
            ->orderBy('errors_count', 'asc') 
            ->orderBy('completed_at', 'asc')
            ->orderBy('hints_used', 'asc');

        if ($offset > 0) {
            $query->skip($offset);
        }

        if ($limit !== null) {
            $query->take($limit);
        }

        return $query->get();
    }

    /**
     * Ottiene il numero totale di partecipanti validi
     */
    public function getTotalValidParticipants(): int
    {
        return $this->attempts()
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->count();
    }

    /**
     * Ottiene la posizione di un utente specifico nella classifica
     * 
     * @param User $user L'utente di cui cercare la posizione
     * @return int|null La posizione (1-based) o null se non trovato
     */
    public function getUserPosition(User $user): ?int
    {
        $userAttempt = $this->attempts()
            ->where('user_id', $user->id)
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->first();

        if (!$userAttempt) {
            return null;
        }

        // Conta quanti tentativi sono migliori di quello dell'utente
        $betterAttempts = $this->attempts()
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->where(function ($query) use ($userAttempt) {
                $query->where('duration_ms', '<', $userAttempt->duration_ms)
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', '=', $userAttempt->duration_ms)
                          ->where('errors_count', '<', $userAttempt->errors_count);
                    })
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', '=', $userAttempt->duration_ms)
                          ->where('errors_count', '=', $userAttempt->errors_count)
                          ->where('completed_at', '<', $userAttempt->completed_at);
                    })
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', '=', $userAttempt->duration_ms)
                          ->where('errors_count', '=', $userAttempt->errors_count)
                          ->where('completed_at', '=', $userAttempt->completed_at)
                          ->where('hints_used', '<', $userAttempt->hints_used);
                    });
            })
            ->count();

        return $betterAttempts + 1;
    }
}
