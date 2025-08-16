<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'description',
        'is_public',
    ];

    protected $casts = [
        'data' => 'array',
        'is_public' => 'boolean',
    ];

    // Tipi di attività disponibili
    public const TYPE_CHALLENGE_COMPLETED = 'challenge_completed';
    public const TYPE_NEW_PERSONAL_RECORD = 'new_personal_record';
    public const TYPE_STREAK_MILESTONE = 'streak_milestone';
    public const TYPE_BADGE_EARNED = 'badge_earned';
    public const TYPE_FRIEND_ADDED = 'friend_added';
    public const TYPE_CLUB_JOINED = 'club_joined';

    /**
     * Relazione: attività appartiene a un utente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crea un'attività per sfida completata
     */
    public static function createChallengeCompleted(User $user, array $challengeData): self
    {
        if (!$user->hasActivityFeedVisible()) {
            return new self(); // Non salvare se l'utente ha disabilitato il feed
        }

        $description = __('app.activity.challenge_completed', [
            'user' => $user->name,
            'difficulty' => __('app.difficulty.' . $challengeData['difficulty']),
            'time' => self::formatTime($challengeData['duration_ms']),
        ]);

        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_CHALLENGE_COMPLETED,
            'data' => $challengeData,
            'description' => $description,
            'is_public' => true,
        ]);
    }

    /**
     * Crea un'attività per nuovo record personale
     */
    public static function createPersonalRecord(User $user, array $recordData): self
    {
        if (!$user->hasActivityFeedVisible()) {
            return new self();
        }

        $description = __('app.activity.new_personal_record', [
            'user' => $user->name,
            'difficulty' => __('app.difficulty.' . $recordData['difficulty']),
            'time' => self::formatTime($recordData['new_best_time']),
        ]);

        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_NEW_PERSONAL_RECORD,
            'data' => $recordData,
            'description' => $description,
            'is_public' => true,
        ]);
    }

    /**
     * Crea un'attività per milestone di streak
     */
    public static function createStreakMilestone(User $user, int $streakDays): self
    {
        if (!$user->hasActivityFeedVisible()) {
            return new self();
        }

        $description = __('app.activity.streak_milestone', [
            'user' => $user->name,
            'days' => $streakDays,
        ]);

        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_STREAK_MILESTONE,
            'data' => ['streak_days' => $streakDays],
            'description' => $description,
            'is_public' => true,
        ]);
    }

    /**
     * Crea un'attività per amicizia aggiunta
     */
    public static function createFriendAdded(User $user, User $friend): self
    {
        if (!$user->hasActivityFeedVisible()) {
            return new self();
        }

        $description = __('app.activity.friend_added', [
            'user' => $user->name,
            'friend' => $friend->name,
        ]);

        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_FRIEND_ADDED,
            'data' => ['friend_id' => $friend->id, 'friend_name' => $friend->name],
            'description' => $description,
            'is_public' => true,
        ]);
    }

    /**
     * Ottiene il feed di attività per un utente (delle proprie attività + degli amici)
     */
    public static function getFeedForUser(User $user, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $friendIds = $user->friends()->pluck('id')->toArray();
        $friendIds[] = $user->id; // Includi anche le proprie attività

        return self::whereIn('user_id', $friendIds)
            ->where('is_public', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Formatta il tempo in millisecondi
     */
    private static function formatTime(int $milliseconds): string
    {
        $totalSeconds = round($milliseconds / 1000);
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }

    /**
     * Ottiene l'icona per il tipo di attività
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CHALLENGE_COMPLETED => '🎯',
            self::TYPE_NEW_PERSONAL_RECORD => '🏆',
            self::TYPE_STREAK_MILESTONE => '🔥',
            self::TYPE_BADGE_EARNED => '🎖️',
            self::TYPE_FRIEND_ADDED => '👥',
            self::TYPE_CLUB_JOINED => '🏛️',
            default => '📝',
        };
    }

    /**
     * Ottiene il colore per il tipo di attività
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CHALLENGE_COMPLETED => 'text-blue-600 dark:text-blue-400',
            self::TYPE_NEW_PERSONAL_RECORD => 'text-yellow-600 dark:text-yellow-400',
            self::TYPE_STREAK_MILESTONE => 'text-red-600 dark:text-red-400',
            self::TYPE_BADGE_EARNED => 'text-purple-600 dark:text-purple-400',
            self::TYPE_FRIEND_ADDED => 'text-green-600 dark:text-green-400',
            self::TYPE_CLUB_JOINED => 'text-indigo-600 dark:text-indigo-400',
            default => 'text-neutral-600 dark:text-neutral-400',
        };
    }

    /**
     * Genera dinamicamente la descrizione dell'attività in base alla lingua corrente
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        // Assicurati che la relazione user sia caricata
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }

        return match ($this->type) {
            self::TYPE_CHALLENGE_COMPLETED => __('app.activity.challenge_completed', [
                'user' => $this->user->name,
                'difficulty' => __('app.difficulty.' . $this->data['difficulty']),
                'time' => self::formatTime($this->data['duration_ms']),
            ]),
            self::TYPE_NEW_PERSONAL_RECORD => __('app.activity.new_personal_record', [
                'user' => $this->user->name,
                'difficulty' => __('app.difficulty.' . $this->data['difficulty']),
                'time' => self::formatTime($this->data['new_best_time']),
            ]),
            self::TYPE_STREAK_MILESTONE => __('app.activity.streak_milestone', [
                'user' => $this->user->name,
                'days' => $this->data['streak_days'],
            ]),
            self::TYPE_FRIEND_ADDED => __('app.activity.friend_added', [
                'user' => $this->user->name,
                'friend' => $this->data['friend_name'],
            ]),
            default => $this->description,
        };
    }
}
