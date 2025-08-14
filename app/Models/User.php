<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
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
}
