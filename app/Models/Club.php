<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Modello per gestire i club/gruppi di utenti
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $owner_id
 * @property string $visibility
 * @property string|null $invite_code
 * @property int $max_members
 * @property array|null $settings
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<ClubMember> $memberships
 * @property-read \Illuminate\Database\Eloquent\Collection<User> $members
 */
class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'visibility',
        'invite_code',
        'max_members',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Visibilità possibili per il club
     */
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_INVITE_ONLY = 'invite_only';

    /**
     * Boot del modello
     */
    protected static function boot()
    {
        parent::boot();

        // Genera automaticamente uno slug quando viene creato un club
        static::creating(function ($club) {
            if (empty($club->slug)) {
                $club->slug = Str::slug($club->name);
                
                // Assicura che lo slug sia unico
                $originalSlug = $club->slug;
                $counter = 1;
                while (static::where('slug', $club->slug)->exists()) {
                    $club->slug = $originalSlug . '-' . $counter++;
                }
            }

            // Genera codice invito per club privati
            if (in_array($club->visibility, [self::VISIBILITY_PRIVATE, self::VISIBILITY_INVITE_ONLY])) {
                $club->invite_code = $club->generateInviteCode();
            }
        });
    }

    /**
     * Relazione con il proprietario del club
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relazione con le membership (club_members)
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Relazione con i membri attivi
     */
    public function activeMembers(): HasMany
    {
        return $this->hasMany(ClubMember::class)->where('status', ClubMember::STATUS_ACTIVE);
    }

    /**
     * Relazione molti-a-molti con gli utenti attraverso club_members
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members')
                    ->withPivot(['role', 'status', 'joined_at', 'invited_at', 'invited_by', 'invite_message'])
                    ->withTimestamps()
                    ->wherePivot('status', ClubMember::STATUS_ACTIVE);
    }

    /**
     * Relazione con i membri invitati
     */
    public function invitedMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members')
                    ->withPivot(['role', 'status', 'joined_at', 'invited_at', 'invited_by', 'invite_message'])
                    ->withTimestamps()
                    ->wherePivot('status', ClubMember::STATUS_INVITED);
    }

    /**
     * Scope per club pubblici
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /**
     * Scope per club attivi
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Genera un codice di invito unico
     */
    public function generateInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Verifica se l'utente è proprietario del club
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Verifica se l'utente è admin del club
     */
    public function isAdmin(User $user): bool
    {
        return $this->memberships()
                    ->where('user_id', $user->id)
                    ->where('status', ClubMember::STATUS_ACTIVE)
                    ->whereIn('role', [ClubMember::ROLE_OWNER, ClubMember::ROLE_ADMIN])
                    ->exists();
    }

    /**
     * Verifica se l'utente è membro del club
     */
    public function isMember(User $user): bool
    {
        return $this->memberships()
                    ->where('user_id', $user->id)
                    ->where('status', ClubMember::STATUS_ACTIVE)
                    ->exists();
    }

    /**
     * Verifica se l'utente ha un invito pendente
     */
    public function hasPendingInvite(User $user): bool
    {
        return $this->memberships()
                    ->where('user_id', $user->id)
                    ->where('status', ClubMember::STATUS_INVITED)
                    ->exists();
    }

    /**
     * Verifica se il club ha raggiunto il limite di membri
     */
    public function isFull(): bool
    {
        return $this->activeMembers()->count() >= $this->max_members;
    }

    /**
     * Ottiene il numero di membri attivi
     */
    public function getMembersCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    /**
     * Route key name per il routing
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
