<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'notify_new_challenges',
        'notify_weekly_challenges',
        'last_notification_sent_at',
        'profile_visibility',
        'stats_visibility',
        'friend_requests_enabled',
        'show_online_status',
        'activity_feed_visible',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_new_challenges' => 'boolean',
            'notify_weekly_challenges' => 'boolean',
            'last_notification_sent_at' => 'datetime',
            'friend_requests_enabled' => 'boolean',
            'show_online_status' => 'boolean',
            'activity_feed_visible' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get all available roles
     */
    public static function getAvailableRoles(): array
    {
        return [
            'user' => 'Utente',
            'admin' => 'Amministratore', 
            'super_admin' => 'Super Amministratore',
        ];
    }

    // =============================================
    // PRIVACY METHODS
    // =============================================

    /**
     * Verifica se il profilo è visibile a un utente
     */
    public function isProfileVisibleTo(?User $viewer): bool
    {
        // Se il viewer è il proprietario del profilo, può sempre vederlo
        if ($viewer && $viewer->id === $this->id) {
            return true;
        }

        // Se il viewer è admin, può sempre vedere tutto
        if ($viewer && $viewer->isAdmin()) {
            return true;
        }

        return match ($this->profile_visibility) {
            'public' => true,
            'friends' => $viewer && $this->isFriendWith($viewer),
            'private' => false,
            default => false,
        };
    }

    /**
     * Verifica se le statistiche sono visibili a un utente
     */
    public function areStatsVisibleTo(?User $viewer): bool
    {
        // Se il viewer è il proprietario del profilo, può sempre vederle
        if ($viewer && $viewer->id === $this->id) {
            return true;
        }

        // Se il viewer è admin, può sempre vedere tutto
        if ($viewer && $viewer->isAdmin()) {
            return true;
        }

        return match ($this->stats_visibility) {
            'public' => true,
            'friends' => $viewer && $this->isFriendWith($viewer),
            'private' => false,
            default => false,
        };
    }

    /**
     * Verifica se l'utente può ricevere richieste di amicizia
     */
    public function canReceiveFriendRequests(): bool
    {
        return $this->friend_requests_enabled;
    }

    /**
     * Verifica se l'utente mostra lo status online agli amici
     */
    public function showsOnlineStatusToFriends(): bool
    {
        return $this->show_online_status;
    }

    /**
     * Verifica se le attività dell'utente appaiono nell'activity feed
     */
    public function hasActivityFeedVisible(): bool
    {
        return $this->activity_feed_visible;
    }

    /**
     * Ottiene le opzioni disponibili per la visibilità
     */
    public static function getVisibilityOptions(): array
    {
        return [
            'public' => __('app.privacy.public'),
            'friends' => __('app.privacy.friends_only'),
            'private' => __('app.privacy.private')
        ];
    }

    /**
     * Relazione: un utente ha un profilo esteso
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Relazione: un utente può creare molte sfide
     */
    public function createdChallenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'created_by');
    }

    /**
     * Relazione: un utente può avere molti tentativi
     */
    public function challengeAttempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class);
    }

    /**
     * Relazione: tentativi completati dell'utente
     */
    public function completedAttempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class)
            ->whereNotNull('completed_at')
            ->where('valid', true);
    }

    /**
     * Get user consents for GDPR compliance
     */
    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    /**
     * Ottiene o crea il profilo dell'utente
     */
    public function getOrCreateProfile(): UserProfile
    {
        return $this->profile ?? $this->profile()->create();
    }

    /**
     * Ottiene statistiche dettagliate per difficoltà
     */
    public function getStatsByDifficulty(): array
    {
        $stats = [];
        $difficulties = ['easy', 'normal', 'hard', 'expert', 'crazy'];

        foreach ($difficulties as $difficulty) {
            $attempts = $this->completedAttempts()
                ->whereHas('challenge.puzzle', function($query) use ($difficulty) {
                    $query->where('difficulty', $difficulty);
                });

            $stats[$difficulty] = [
                'completed' => $attempts->count(),
                'best_time' => $attempts->min('duration_ms'),
                'average_time' => $attempts->avg('duration_ms'),
                'total_errors' => $attempts->sum('errors_count'),
                'total_hints' => $attempts->sum('hints_used'),
                'completion_rate' => $this->getCompletionRateByDifficulty($difficulty),
            ];
        }

        return $stats;
    }

    /**
     * Calcola percentuale completamento per difficoltà
     */
    public function getCompletionRateByDifficulty(string $difficulty): float
    {
        $totalChallenges = \App\Models\Challenge::whereHas('puzzle', function($query) use ($difficulty) {
            $query->where('difficulty', $difficulty);
        })->count();

        if ($totalChallenges === 0) {
            return 0.0;
        }

        $completedChallenges = $this->completedAttempts()
            ->whereHas('challenge.puzzle', function($query) use ($difficulty) {
                $query->where('difficulty', $difficulty);
            })
            ->distinct('challenge_id')
            ->count();

        return ($completedChallenges / $totalChallenges) * 100;
    }

    /**
     * Ottiene statistiche per tipo di sfida
     */
    public function getStatsByChallengeType(): array
    {
        $stats = [];
        $types = ['daily', 'weekly', 'custom'];

        foreach ($types as $type) {
            $attempts = $this->completedAttempts()
                ->whereHas('challenge', function($query) use ($type) {
                    $query->where('type', $type);
                });

            $stats[$type] = [
                'completed' => $attempts->count(),
                'best_time' => $attempts->min('duration_ms'),
                'average_time' => $attempts->avg('duration_ms'),
                'completion_rate' => $this->getCompletionRateByChallengeType($type),
            ];
        }

        return $stats;
    }

    /**
     * Calcola percentuale completamento per tipo sfida
     */
    public function getCompletionRateByChallengeType(string $type): float
    {
        $totalChallenges = \App\Models\Challenge::where('type', $type)
            ->where('status', 'active')
            ->count();

        if ($totalChallenges === 0) {
            return 0.0;
        }

        $completedChallenges = $this->completedAttempts()
            ->whereHas('challenge', function($query) use ($type) {
                $query->where('type', $type);
            })
            ->distinct('challenge_id')
            ->count();

        return ($completedChallenges / $totalChallenges) * 100;
    }

    /**
     * Ottiene trend performance negli ultimi mesi
     */
    public function getPerformanceTrend(int $months = 6): array
    {
        $trend = [];
        $startDate = now()->subMonths($months);

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $startDate->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthAttempts = $this->completedAttempts()
                ->whereBetween('completed_at', [$monthStart, $monthEnd]);

            $trend[] = [
                'month' => $monthStart->format('Y-m'),
                'month_name' => $monthStart->format('M Y'),
                'completed' => $monthAttempts->count(),
                'average_time' => $monthAttempts->avg('duration_ms'),
                'best_time' => $monthAttempts->min('duration_ms'),
            ];
        }

        return $trend;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    // =============================================
    // FRIENDSHIP METHODS
    // =============================================

    /**
     * Relazione: richieste di amicizia inviate da questo utente
     */
    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Relazione: richieste di amicizia ricevute da questo utente
     */
    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Ottiene tutti gli amici accettati (sia inviati che ricevuti)
     */
    public function friends()
    {
        // Amicizie dove questo utente ha inviato la richiesta
        $sentFriendships = $this->sentFriendRequests()
            ->accepted()
            ->with('friend')
            ->get();

        // Amicizie dove questo utente ha ricevuto la richiesta
        $receivedFriendships = $this->receivedFriendRequests()
            ->accepted() 
            ->with('user')
            ->get();

        $friends = collect();

        // Aggiungi amici dalle richieste inviate
        foreach ($sentFriendships as $friendship) {
            $friend = $friendship->friend;
            $friend->pivot = $friendship; // Aggiungi l'oggetto friendship come pivot
            $friends->push($friend);
        }

        // Aggiungi amici dalle richieste ricevute
        foreach ($receivedFriendships as $friendship) {
            $friend = $friendship->user;
            $friend->pivot = $friendship; // Aggiungi l'oggetto friendship come pivot
            
            // Evita duplicati
            if (!$friends->contains('id', $friend->id)) {
                $friends->push($friend);
            }
        }

        return $friends;
    }

    /**
     * Ottiene le richieste di amicizia in attesa (ricevute)
     */
    public function pendingFriendRequests()
    {
        return $this->receivedFriendRequests()
            ->pending()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verifica se questo utente è amico di un altro utente
     */
    public function isFriendWith(User $user): bool
    {
        return Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $this->id)
                  ->where('friend_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $this->id);
        })->accepted()->exists();
    }

    /**
     * Verifica se esiste una richiesta di amicizia pendente tra gli utenti
     */
    public function hasPendingFriendRequestWith(User $user): bool
    {
        return Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $this->id)
                  ->where('friend_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $this->id);
        })->pending()->exists();
    }

    /**
     * Verifica se questo utente ha bloccato un altro utente
     */
    public function hasBlocked(User $user): bool
    {
        return $this->sentFriendRequests()
            ->where('friend_id', $user->id)
            ->blocked()
            ->exists();
    }

    /**
     * Verifica se questo utente è stato bloccato da un altro utente
     */
    public function isBlockedBy(User $user): bool
    {
        return $this->receivedFriendRequests()
            ->where('user_id', $user->id)
            ->blocked()
            ->exists();
    }

    /**
     * Conta il numero di amici
     */
    public function getFriendsCountAttribute(): int
    {
        $sent = $this->sentFriendRequests()->accepted()->count();
        $received = $this->receivedFriendRequests()->accepted()->count();
        return $sent + $received;
    }

    // =============================================
    // CLUB METHODS
    // =============================================

    /**
     * Relazione: club posseduti da questo utente
     */
    public function ownedClubs(): HasMany
    {
        return $this->hasMany(Club::class, 'owner_id');
    }

    /**
     * Relazione: appartenenze ai club
     */
    public function clubMemberships(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Relazione: club di cui è membro attivo
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_members')
                    ->withPivot(['role', 'status', 'joined_at', 'invited_at', 'invited_by', 'invite_message'])
                    ->withTimestamps()
                    ->wherePivot('status', ClubMember::STATUS_ACTIVE);
    }

    /**
     * Relazione: inviti ai club pendenti
     */
    public function clubInvites(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_members')
                    ->withPivot(['role', 'status', 'joined_at', 'invited_at', 'invited_by', 'invite_message'])
                    ->withTimestamps()
                    ->wherePivot('status', ClubMember::STATUS_INVITED);
    }

    /**
     * Verifica se l'utente è membro di un club
     */
    public function isMemberOf(Club $club): bool
    {
        return $this->clubMemberships()
                    ->where('club_id', $club->id)
                    ->where('status', ClubMember::STATUS_ACTIVE)
                    ->exists();
    }

    /**
     * Verifica se l'utente è proprietario di un club
     */
    public function isOwnerOf(Club $club): bool
    {
        return $club->owner_id === $this->id;
    }

    /**
     * Verifica se l'utente è admin di un club
     */
    public function isAdminOf(Club $club): bool
    {
        return $this->clubMemberships()
                    ->where('club_id', $club->id)
                    ->where('status', ClubMember::STATUS_ACTIVE)
                    ->whereIn('role', [ClubMember::ROLE_OWNER, ClubMember::ROLE_ADMIN])
                    ->exists();
    }

    /**
     * Ottiene il ruolo dell'utente in un club
     */
    public function getRoleInClub(Club $club): ?string
    {
        $membership = $this->clubMemberships()
                           ->where('club_id', $club->id)
                           ->where('status', ClubMember::STATUS_ACTIVE)
                           ->first();

        return $membership?->role;
    }

    /**
     * Conta il numero di club di cui è membro
     */
    public function getClubsCountAttribute(): int
    {
        return $this->clubs()->count();
    }
}
