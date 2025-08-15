<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\ChallengeAttempt;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

/**
 * Observer per invalidazione cache automatica quando cambiano i tentativi
 */
class ChallengeAttemptObserver
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Handle the ChallengeAttempt "created" event.
     */
    public function created(ChallengeAttempt $challengeAttempt): void
    {
        $this->invalidateRelatedCache($challengeAttempt);
    }

    /**
     * Handle the ChallengeAttempt "updated" event.
     */
    public function updated(ChallengeAttempt $challengeAttempt): void
    {
        // Se cambia qualcosa che influenza le leaderboard
        if ($challengeAttempt->wasChanged([
            'completed_at', 'valid', 'duration_ms', 'errors_count', 'hints_used',
            'move_validation_passed', 'flagged_for_review'
        ])) {
            $this->invalidateRelatedCache($challengeAttempt);
        }
    }

    /**
     * Handle the ChallengeAttempt "deleted" event.
     */
    public function deleted(ChallengeAttempt $challengeAttempt): void
    {
        $this->invalidateRelatedCache($challengeAttempt);
    }

    /**
     * Invalida cache correlate
     */
    private function invalidateRelatedCache(ChallengeAttempt $challengeAttempt): void
    {
        try {
            // Invalida leaderboard della sfida
            $this->cacheService->invalidateLeaderboardCache($challengeAttempt->challenge_id);
            
            // Invalida cache utente
            $this->cacheService->invalidateUserCache($challengeAttempt->user_id);
            
            // Invalida statistiche sfida
            $this->cacheService->invalidateChallengeCache($challengeAttempt->challenge_id);
            
            Log::debug('Cache invalidated for challenge attempt', [
                'attempt_id' => $challengeAttempt->id,
                'challenge_id' => $challengeAttempt->challenge_id,
                'user_id' => $challengeAttempt->user_id,
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate cache', [
                'attempt_id' => $challengeAttempt->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
