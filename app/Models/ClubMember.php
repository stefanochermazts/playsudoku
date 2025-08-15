<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Modello per gestire i membri dei club
 * 
 * @property int $id
 * @property int $club_id
 * @property int $user_id
 * @property string $role
 * @property string $status
 * @property Carbon|null $joined_at
 * @property Carbon|null $invited_at
 * @property int|null $invited_by
 * @property string|null $invite_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read Club $club
 * @property-read User $user
 * @property-read User|null $inviter
 */
class ClubMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'invited_at',
        'invited_by',
        'invite_message',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'invited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Ruoli possibili nel club
     */
    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';

    /**
     * Stati possibili dell'appartenenza
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INVITED = 'invited';
    public const STATUS_BANNED = 'banned';

    /**
     * Relazione con il club
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Relazione con l'utente membro
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con l'utente che ha inviato l'invito
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scope per membri attivi
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope per inviti pendenti
     */
    public function scopeInvited(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INVITED);
    }

    /**
     * Scope per membri bannati
     */
    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BANNED);
    }

    /**
     * Scope per owner
     */
    public function scopeOwners(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_OWNER);
    }

    /**
     * Scope per admin
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope per membri normali
     */
    public function scopeMembers(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_MEMBER);
    }

    /**
     * Accetta l'invito al club
     */
    public function acceptInvite(): bool
    {
        if ($this->status !== self::STATUS_INVITED) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);
    }

    /**
     * Rifiuta l'invito al club
     */
    public function declineInvite(): bool
    {
        if ($this->status !== self::STATUS_INVITED) {
            return false;
        }

        return $this->delete();
    }

    /**
     * Verifica se l'utente è proprietario
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Verifica se l'utente è admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Verifica se l'utente è attivo nel club
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica se l'utente ha un invito pendente
     */
    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    /**
     * Verifica se l'utente è bannato
     */
    public function isBanned(): bool
    {
        return $this->status === self::STATUS_BANNED;
    }
}
