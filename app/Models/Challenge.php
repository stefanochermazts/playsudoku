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
}
