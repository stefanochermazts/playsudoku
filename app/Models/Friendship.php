<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Modello per gestire le amicizie tra utenti
 * 
 * @property int $id
 * @property int $user_id Utente che invia la richiesta
 * @property int $friend_id Utente che riceve la richiesta
 * @property string $status Stato dell'amicizia (pending, accepted, blocked, declined)
 * @property Carbon|null $accepted_at Timestamp di accettazione
 * @property string|null $message Messaggio opzionale con la richiesta
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read User $user Utente che ha inviato la richiesta
 * @property-read User $friend Utente che ha ricevuto la richiesta
 */
class Friendship extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'friend_id', 
        'status',
        'accepted_at',
        'message',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Stati possibili per l'amicizia
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DECLINED = 'declined';

    /**
     * Relazione con l'utente che ha inviato la richiesta
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relazione con l'utente che ha ricevuto la richiesta
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Scope per amicizie accettate
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope per richieste in attesa
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope per amicizie bloccate
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }

    /**
     * Accetta la richiesta di amicizia
     */
    public function accept(): bool
    {
        return $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
    }

    /**
     * Rifiuta la richiesta di amicizia
     */
    public function decline(): bool
    {
        return $this->update([
            'status' => self::STATUS_DECLINED,
        ]);
    }

    /**
     * Blocca l'utente
     */
    public function block(): bool
    {
        return $this->update([
            'status' => self::STATUS_BLOCKED,
        ]);
    }

    /**
     * Verifica se l'amicizia è accettata
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Verifica se la richiesta è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se l'utente è bloccato
     */
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }
}
