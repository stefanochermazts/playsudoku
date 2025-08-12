<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Servizio per la gestione delle classifiche
 */
class LeaderboardService
{
    /**
     * Ottiene la classifica per una sfida specifica
     */
    public function getChallengeLeaderboard(
        Challenge $challenge,
        int $limit = 100,
        int $page = 1
    ): LengthAwarePaginator {
        $cacheKey = "leaderboard_challenge_{$challenge->id}_limit_{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($challenge, $limit, $page) {
            $query = ChallengeAttempt::where('challenge_id', $challenge->id)
                ->valid()
                ->completed()
                ->with(['user', 'user.profile'])
                ->byBestTime();

            return $query->paginate($limit, ['*'], 'page', $page);
        });
    }

    /**
     * Ottiene la classifica globale per periodo
     */
    public function getGlobalLeaderboard(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        string $challengeType = 'all',
        string $difficulty = 'all',
        int $limit = 100
    ): Collection {
        $cacheKey = $this->buildGlobalLeaderboardCacheKey(
            $startDate, $endDate, $challengeType, $difficulty, $limit
        );

        return Cache::remember($cacheKey, 600, function () use (
            $startDate, $endDate, $challengeType, $difficulty, $limit
        ) {
            $query = DB::table('challenge_attempts')
                ->join('challenges', 'challenge_attempts.challenge_id', '=', 'challenges.id')
                ->join('puzzles', 'challenges.puzzle_id', '=', 'puzzles.id')
                ->join('users', 'challenge_attempts.user_id', '=', 'users.id')
                ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->where('challenge_attempts.valid', true)
                ->whereNotNull('challenge_attempts.completed_at')
                ->select([
                    'users.id as user_id',
                    'users.name as user_name',
                    'user_profiles.country',
                    DB::raw('COUNT(*) as completed_challenges'),
                    DB::raw('AVG(challenge_attempts.duration_ms) as avg_duration'),
                    DB::raw('MIN(challenge_attempts.duration_ms) as best_time'),
                    DB::raw('SUM(challenge_attempts.errors_count) as total_errors'),
                    DB::raw('SUM(challenge_attempts.hints_used) as total_hints'),
                ])
                ->groupBy([
                    'users.id', 'users.name', 'user_profiles.country'
                ]);

            // Filtri opzionali
            if ($startDate) {
                $query->where('challenge_attempts.completed_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('challenge_attempts.completed_at', '<=', $endDate);
            }

            if ($challengeType !== 'all') {
                $query->where('challenges.type', $challengeType);
            }

            if ($difficulty !== 'all') {
                $query->where('puzzles.difficulty', $difficulty);
            }

            return $query->orderByDesc('completed_challenges')
                ->orderBy('avg_duration')
                ->limit($limit)
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'user_id' => $row->user_id,
                        'user_name' => $row->user_name,
                        'country' => $row->country,
                        'completed_challenges' => (int) $row->completed_challenges,
                        'avg_duration' => (float) $row->avg_duration,
                        'best_time' => (int) $row->best_time,
                        'total_errors' => (int) $row->total_errors,
                        'total_hints' => (int) $row->total_hints,
                        'avg_duration_formatted' => $this->formatDuration((int) $row->avg_duration),
                        'best_time_formatted' => $this->formatDuration((int) $row->best_time),
                    ];
                });
        });
    }

    /**
     * Ottiene la classifica delle sfide daily
     */
    public function getDailyLeaderboard(
        ?Carbon $date = null,
        int $limit = 50
    ): Collection {
        $date = $date ?? now();
        $cacheKey = "daily_leaderboard_{$date->toDateString()}_limit_{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($date, $limit) {
            $challenge = Challenge::where('type', 'daily')
                ->whereDate('starts_at', $date->toDateString())
                ->first();

            if (!$challenge) {
                return collect();
            }

            return $this->getChallengeLeaderboard($challenge, $limit)->items();
        });
    }

    /**
     * Ottiene la classifica delle sfide weekly
     */
    public function getWeeklyLeaderboard(
        ?Carbon $startOfWeek = null,
        int $limit = 50
    ): Collection {
        $startOfWeek = $startOfWeek ?? now()->startOfWeek();
        $cacheKey = "weekly_leaderboard_{$startOfWeek->toDateString()}_limit_{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($startOfWeek, $limit) {
            $challenge = Challenge::where('type', 'weekly')
                ->whereBetween('starts_at', [
                    $startOfWeek,
                    $startOfWeek->copy()->endOfWeek()
                ])
                ->first();

            if (!$challenge) {
                return collect();
            }

            return $this->getChallengeLeaderboard($challenge, $limit)->items();
        });
    }

    /**
     * Ottiene la posizione di un utente in una sfida
     */
    public function getUserRankInChallenge(User $user, Challenge $challenge): ?int
    {
        $userAttempt = $challenge->attempts()
            ->where('user_id', $user->id)
            ->valid()
            ->completed()
            ->first();

        if (!$userAttempt) {
            return null;
        }

        return ChallengeAttempt::where('challenge_id', $challenge->id)
            ->valid()
            ->completed()
            ->where(function ($query) use ($userAttempt) {
                $query->where('duration_ms', '<', $userAttempt->duration_ms)
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', $userAttempt->duration_ms)
                            ->where('errors_count', '<', $userAttempt->errors_count);
                    })
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', $userAttempt->duration_ms)
                            ->where('errors_count', $userAttempt->errors_count)
                            ->where('completed_at', '<', $userAttempt->completed_at);
                    })
                    ->orWhere(function ($q) use ($userAttempt) {
                        $q->where('duration_ms', $userAttempt->duration_ms)
                            ->where('errors_count', $userAttempt->errors_count)
                            ->where('completed_at', $userAttempt->completed_at)
                            ->where('hints_used', '<', $userAttempt->hints_used);
                    });
            })
            ->count() + 1;
    }

    /**
     * Ottiene le statistiche di un utente
     */
    public function getUserStats(User $user): array
    {
        $cacheKey = "user_stats_{$user->id}";

        return Cache::remember($cacheKey, 900, function () use ($user) {
            $attempts = ChallengeAttempt::where('user_id', $user->id)
                ->valid()
                ->completed()
                ->with(['challenge.puzzle'])
                ->get();

            if ($attempts->isEmpty()) {
                return [
                    'total_completed' => 0,
                    'best_time' => null,
                    'avg_time' => null,
                    'total_errors' => 0,
                    'total_hints' => 0,
                    'by_difficulty' => [],
                    'by_type' => [],
                    'recent_attempts' => [],
                ];
            }

            $byDifficulty = $attempts->groupBy(fn($attempt) => $attempt->challenge->puzzle->difficulty)
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'best_time' => $group->min('duration_ms'),
                        'avg_time' => $group->avg('duration_ms'),
                    ];
                });

            $byType = $attempts->groupBy(fn($attempt) => $attempt->challenge->type)
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'best_time' => $group->min('duration_ms'),
                        'avg_time' => $group->avg('duration_ms'),
                    ];
                });

            return [
                'total_completed' => $attempts->count(),
                'best_time' => $attempts->min('duration_ms'),
                'avg_time' => $attempts->avg('duration_ms'),
                'total_errors' => $attempts->sum('errors_count'),
                'total_hints' => $attempts->sum('hints_used'),
                'by_difficulty' => $byDifficulty->toArray(),
                'by_type' => $byType->toArray(),
                'recent_attempts' => $attempts->sortByDesc('completed_at')
                    ->take(10)
                    ->values()
                    ->toArray(),
            ];
        });
    }

    /**
     * Ottiene il confronto tra due utenti
     */
    public function compareUsers(User $user1, User $user2): array
    {
        $stats1 = $this->getUserStats($user1);
        $stats2 = $this->getUserStats($user2);

        return [
            'user1' => array_merge(['name' => $user1->name], $stats1),
            'user2' => array_merge(['name' => $user2->name], $stats2),
            'comparison' => [
                'total_completed_diff' => $stats1['total_completed'] - $stats2['total_completed'],
                'best_time_comparison' => $this->compareTimes($stats1['best_time'], $stats2['best_time']),
                'avg_time_comparison' => $this->compareTimes($stats1['avg_time'], $stats2['avg_time']),
            ],
        ];
    }

    /**
     * Invalida le cache delle classifiche
     */
    public function clearLeaderboardCache(Challenge $challenge): void
    {
        $patterns = [
            "leaderboard_challenge_{$challenge->id}_*",
            "user_stats_*",
        ];

        foreach ($patterns as $pattern) {
            // In una implementazione reale, useresti Redis SCAN con pattern
            // Per ora invalidiamo manualmente le cache che conosciamo
            Cache::forget("leaderboard_challenge_{$challenge->id}_limit_100");
            Cache::forget("leaderboard_challenge_{$challenge->id}_limit_50");
        }

        // Invalida anche cache globali se necessario
        if ($challenge->type === 'daily') {
            $date = $challenge->starts_at->toDateString();
            Cache::forget("daily_leaderboard_{$date}_limit_50");
        }

        if ($challenge->type === 'weekly') {
            $date = $challenge->starts_at->toDateString();
            Cache::forget("weekly_leaderboard_{$date}_limit_50");
        }
    }

    /**
     * Ottiene le tendenze delle performance
     */
    public function getPerformanceTrends(
        User $user,
        int $days = 30
    ): array {
        $startDate = now()->subDays($days);

        $attempts = ChallengeAttempt::where('user_id', $user->id)
            ->valid()
            ->completed()
            ->where('completed_at', '>=', $startDate)
            ->orderBy('completed_at')
            ->get();

        $trends = [];
        foreach ($attempts as $attempt) {
            $date = $attempt->completed_at->toDateString();
            if (!isset($trends[$date])) {
                $trends[$date] = [
                    'date' => $date,
                    'attempts' => 0,
                    'total_time' => 0,
                    'total_errors' => 0,
                ];
            }

            $trends[$date]['attempts']++;
            $trends[$date]['total_time'] += $attempt->duration_ms;
            $trends[$date]['total_errors'] += $attempt->errors_count;
        }

        return array_values($trends);
    }

    /**
     * Formatta la durata in formato leggibile
     */
    private function formatDuration(int $durationMs): string
    {
        $totalSeconds = intval($durationMs / 1000);
        $minutes = intval($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Costruisce la chiave cache per leaderboard globale
     */
    private function buildGlobalLeaderboardCacheKey(
        ?Carbon $startDate,
        ?Carbon $endDate,
        string $challengeType,
        string $difficulty,
        int $limit
    ): string {
        $parts = [
            'global_leaderboard',
            $startDate ? $startDate->toDateString() : 'null',
            $endDate ? $endDate->toDateString() : 'null',
            $challengeType,
            $difficulty,
            $limit
        ];

        return implode('_', $parts);
    }

    /**
     * Confronta due tempi
     */
    private function compareTimes(?float $time1, ?float $time2): string
    {
        if ($time1 === null && $time2 === null) return 'equal';
        if ($time1 === null) return 'user2_better';
        if ($time2 === null) return 'user1_better';

        if ($time1 < $time2) return 'user1_better';
        if ($time1 > $time2) return 'user2_better';
        return 'equal';
    }
}
