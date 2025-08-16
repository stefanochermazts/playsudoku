<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\User;
use Carbon\Carbon;

/**
 * Servizio centralizzato per cache performance con Redis
 */
class CacheService
{
    // TTL Cache configurations (in seconds)
    private const TTL_LEADERBOARD = 300; // 5 minuti
    private const TTL_CHALLENGE_DETAILS = 600; // 10 minuti
    private const TTL_USER_STATS = 900; // 15 minuti
    private const TTL_PUZZLE_DATA = 1800; // 30 minuti
    private const TTL_DAILY_STATS = 3600; // 1 ora
    private const TTL_SHORT = 60; // 1 minuto per dati volatili
    
    // Cache key prefixes
    private const PREFIX_LEADERBOARD = 'leaderboard';
    private const PREFIX_CHALLENGE = 'challenge';
    private const PREFIX_USER = 'user';
    private const PREFIX_PUZZLE = 'puzzle';
    private const PREFIX_STATS = 'stats';

    /**
     * CACHE: Leaderboard di una sfida specifica
     */
    public function getChallengeLeaderboard(int $challengeId, int $limit = 50): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_LEADERBOARD, "challenge.{$challengeId}.limit.{$limit}");
        
        return Cache::remember($cacheKey, self::TTL_LEADERBOARD, function () use ($challengeId, $limit) {
            return ChallengeAttempt::where('challenge_id', $challengeId)
                ->whereNotNull('completed_at')
                ->where('valid', true)
                // Allinea i filtri a quelli della pagina leaderboard: accetta NULL come valido
                ->where(function($q){
                    $q->where('move_validation_passed', true)
                      ->orWhereNull('move_validation_passed');
                })
                ->where(function($q){
                    $q->where('flagged_for_review', false)
                      ->orWhereNull('flagged_for_review');
                })
                ->with(['user:id,name,email'])
                ->orderByRaw('(duration_ms + (errors_count * 3000))')
                ->orderBy('hints_used')
                ->orderBy('completed_at')
                ->limit($limit)
                ->get()
                ->map(function ($attempt) {
                    return [
                        'user_id' => $attempt->user_id,
                        'user_name' => $attempt->user->name,
                        'duration_ms' => $attempt->duration_ms,
                        'errors_count' => $attempt->errors_count,
                        'hints_used' => $attempt->hints_used,
                        'completed_at' => $attempt->completed_at->toISOString(),
                        'penalized_time' => $attempt->getPenalizedTime(),
                        'formatted_duration' => $attempt->getFormattedPenalizedDuration(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * CACHE: Leaderboard globale (top performers)
     */
    public function getGlobalLeaderboard(string $period = 'all', int $limit = 100): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_LEADERBOARD, "global.{$period}.limit.{$limit}");
        
        return Cache::remember($cacheKey, self::TTL_LEADERBOARD, function () use ($period, $limit) {
            $query = ChallengeAttempt::whereNotNull('completed_at')
                ->where('valid', true)
                ->where('move_validation_passed', '!=', false)
                ->where('flagged_for_review', false)
                ->with(['user:id,name,email', 'challenge:id,type']);
            
            // Filtro per periodo
            switch ($period) {
                case 'daily':
                    $query->whereDate('completed_at', today());
                    break;
                case 'weekly':
                    $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('completed_at', now()->month)
                          ->whereYear('completed_at', now()->year);
                    break;
                // 'all' - no filter
            }
            
            return $query->orderByRaw('(duration_ms + (errors_count * 3000))')
                ->orderBy('hints_used')
                ->orderBy('completed_at')
                ->limit($limit)
                ->get()
                ->map(function ($attempt) {
                    return [
                        'user_id' => $attempt->user_id,
                        'user_name' => $attempt->user->name,
                        'challenge_type' => $attempt->challenge->type,
                        'duration_ms' => $attempt->duration_ms,
                        'errors_count' => $attempt->errors_count,
                        'hints_used' => $attempt->hints_used,
                        'completed_at' => $attempt->completed_at->toISOString(),
                        'penalized_time' => $attempt->getPenalizedTime(),
                        'formatted_duration' => $attempt->getFormattedPenalizedDuration(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * CACHE: Dettagli di una sfida con statistiche
     */
    public function getChallengeDetails(int $challengeId): ?array
    {
        $cacheKey = $this->buildKey(self::PREFIX_CHALLENGE, "details.{$challengeId}");
        
        return Cache::remember($cacheKey, self::TTL_CHALLENGE_DETAILS, function () use ($challengeId) {
            $challenge = Challenge::with(['puzzle:id,givens,solution,difficulty,seed'])
                ->find($challengeId);
            
            if (!$challenge) {
                return null;
            }
            
            // Statistiche partecipazione
            $participationStats = $this->getChallengeParticipationStats($challengeId);
            
            return [
                'id' => $challenge->id,
                'type' => $challenge->type,
                'starts_at' => $challenge->starts_at?->toISOString(),
                'ends_at' => $challenge->ends_at?->toISOString(),
                'status' => $challenge->status,
                'settings' => $challenge->settings,
                'puzzle' => [
                    'id' => $challenge->puzzle->id,
                    'difficulty' => $challenge->puzzle->difficulty,
                    'seed' => $challenge->puzzle->seed,
                    'givens' => $challenge->puzzle->givens,
                ],
                'stats' => $participationStats,
            ];
        });
    }

    /**
     * CACHE: Statistiche partecipazione sfida
     */
    public function getChallengeParticipationStats(int $challengeId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_CHALLENGE, "participation.{$challengeId}");
        
        return Cache::remember($cacheKey, self::TTL_SHORT, function () use ($challengeId) {
            return [
                'total_attempts' => ChallengeAttempt::where('challenge_id', $challengeId)->count(),
                'completed_attempts' => ChallengeAttempt::where('challenge_id', $challengeId)
                    ->whereNotNull('completed_at')->count(),
                'valid_attempts' => ChallengeAttempt::where('challenge_id', $challengeId)
                    ->where('valid', true)->count(),
                'unique_participants' => ChallengeAttempt::where('challenge_id', $challengeId)
                    ->distinct('user_id')->count(),
                'avg_duration' => ChallengeAttempt::where('challenge_id', $challengeId)
                    ->whereNotNull('duration_ms')
                    ->where('valid', true)
                    ->avg('duration_ms'),
                'avg_errors' => ChallengeAttempt::where('challenge_id', $challengeId)
                    ->where('valid', true)
                    ->avg('errors_count'),
            ];
        });
    }

    /**
     * CACHE: Statistiche utente avanzate
     */
    public function getUserStats(int $userId): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_USER, "stats.{$userId}");
        
        return Cache::remember($cacheKey, self::TTL_USER_STATS, function () use ($userId) {
            $attempts = ChallengeAttempt::where('user_id', $userId)
                ->with(['challenge:id,type'])
                ->get();
            
            $completed = $attempts->where('completed_at', '!=', null);
            $valid = $completed->where('valid', true);
            
            return [
                'total_attempts' => $attempts->count(),
                'completed_attempts' => $completed->count(),
                'valid_attempts' => $valid->count(),
                'completion_rate' => $attempts->count() > 0 ? 
                    ($completed->count() / $attempts->count()) * 100 : 0,
                'best_time' => $valid->min('duration_ms'),
                'avg_time' => $valid->avg('duration_ms'),
                'total_errors' => $valid->sum('errors_count'),
                'total_hints' => $valid->sum('hints_used'),
                'by_challenge_type' => $this->getStatsByType($valid),
                'recent_activity' => $this->getRecentActivity($userId),
                'performance_trend' => $this->getPerformanceTrend($userId),
            ];
        });
    }

    /**
     * CACHE: Statistiche per tipo di sfida
     */
    private function getStatsByType($attempts): array
    {
        return $attempts->groupBy('challenge.type')
            ->map(function ($typeAttempts) {
                return [
                    'count' => $typeAttempts->count(),
                    'best_time' => $typeAttempts->min('duration_ms'),
                    'avg_time' => $typeAttempts->avg('duration_ms'),
                    'completion_rate' => 100, // già filtrati per completati
                ];
            })
            ->toArray();
    }

    /**
     * CACHE: Attività recente utente
     */
    private function getRecentActivity(int $userId): array
    {
        return ChallengeAttempt::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->with(['challenge:id,type'])
            ->get()
            ->map(function ($attempt) {
                return [
                    'challenge_id' => $attempt->challenge_id,
                    'challenge_type' => $attempt->challenge->type,
                    'duration_ms' => $attempt->duration_ms,
                    'errors_count' => $attempt->errors_count,
                    'completed_at' => $attempt->completed_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * CACHE: Trend performance utente
     */
    private function getPerformanceTrend(int $userId): array
    {
        $last30Days = ChallengeAttempt::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('valid', true)
            ->where('completed_at', '>=', now()->subDays(30))
            ->orderBy('completed_at')
            ->get(['duration_ms', 'completed_at']);
        
        // Raggruppa per settimana
        return $last30Days->groupBy(function ($attempt) {
            return $attempt->completed_at->format('Y-W');
        })->map(function ($weekAttempts) {
            return [
                'avg_duration' => $weekAttempts->avg('duration_ms'),
                'count' => $weekAttempts->count(),
            ];
        })->toArray();
    }

    /**
     * CACHE: Statistiche giornaliere sistema
     */
    public function getDailySystemStats(): array
    {
        $cacheKey = $this->buildKey(self::PREFIX_STATS, 'daily.' . today()->format('Y-m-d'));
        
        return Cache::remember($cacheKey, self::TTL_DAILY_STATS, function () {
            $today = today();
            
            return [
                'new_users' => User::whereDate('created_at', $today)->count(),
                'active_users' => ChallengeAttempt::whereDate('created_at', $today)
                    ->distinct('user_id')->count(),
                'completed_challenges' => ChallengeAttempt::whereDate('completed_at', $today)
                    ->whereNotNull('completed_at')->count(),
                'total_playtime_minutes' => ChallengeAttempt::whereDate('completed_at', $today)
                    ->whereNotNull('duration_ms')
                    ->sum('duration_ms') / 1000 / 60,
                'avg_completion_time' => ChallengeAttempt::whereDate('completed_at', $today)
                    ->whereNotNull('duration_ms')
                    ->where('valid', true)
                    ->avg('duration_ms'),
                'flagged_attempts' => ChallengeAttempt::whereDate('created_at', $today)
                    ->where('flagged_for_review', true)->count(),
            ];
        });
    }

    /**
     * INVALIDAZIONE: Leaderboard quando cambia un tentativo
     */
    public function invalidateLeaderboardCache(int $challengeId): void
    {
        $this->invalidateByPattern(self::PREFIX_LEADERBOARD . ".challenge.{$challengeId}.*");
        $this->invalidateByPattern(self::PREFIX_LEADERBOARD . ".global.*");
    }

    /**
     * INVALIDAZIONE: Cache challenge quando cambia
     */
    public function invalidateChallengeCache(int $challengeId): void
    {
        $this->invalidateByPattern(self::PREFIX_CHALLENGE . ".{$challengeId}.*");
        $this->invalidateByPattern(self::PREFIX_CHALLENGE . ".details.{$challengeId}");
        $this->invalidateByPattern(self::PREFIX_CHALLENGE . ".participation.{$challengeId}");
    }

    /**
     * INVALIDAZIONE: Cache utente quando cambia
     */
    public function invalidateUserCache(int $userId): void
    {
        $this->invalidateByPattern(self::PREFIX_USER . ".stats.{$userId}");
    }

    /**
     * INVALIDAZIONE: Pattern matching per Redis
     */
    private function invalidateByPattern(string $pattern): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $keys = $redis->keys($this->buildKey($pattern));
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // Fallback per altri cache drivers
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Log errore ma non bloccare l'applicazione
            \Log::warning('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper: Costruisce chiave cache con namespace
     */
    private function buildKey(string ...$parts): string
    {
        return 'playsudoku:' . implode(':', $parts);
    }

    /**
     * Helper: Forza refresh di una chiave specifica
     */
    public function refreshKey(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Helper: Statistiche utilizzo cache
     */
    public function getCacheStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info('memory');
                
                return [
                    'driver' => 'redis',
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                    'keys_count' => $redis->dbsize(),
                    'hits' => $redis->info('stats')['keyspace_hits'] ?? 0,
                    'misses' => $redis->info('stats')['keyspace_misses'] ?? 0,
                ];
            } else {
                return [
                    'driver' => config('cache.default'),
                    'status' => 'active'
                ];
            }
        } catch (\Exception $e) {
            return [
                'driver' => config('cache.default'),
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Helper: Pulizia cache scadute (maintenance)
     */
    public function cleanup(): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                
                // Rimuovi chiavi scadute vecchie di più di 1 giorno
                $oldKeys = $redis->keys($this->buildKey('*'));
                $cleaned = 0;
                
                foreach ($oldKeys as $key) {
                    $ttl = $redis->ttl($key);
                    if ($ttl === -1) { // Nessuna scadenza impostata
                        $redis->expire($key, self::TTL_DAILY_STATS); // Imposta scadenza default
                    } elseif ($ttl === -2) { // Chiave non esiste
                        $cleaned++;
                    }
                }
                
                \Log::info('Cache cleanup completed', ['cleaned_keys' => $cleaned]);
            }
        } catch (\Exception $e) {
            \Log::warning('Cache cleanup failed', ['error' => $e->getMessage()]);
        }
    }
}
