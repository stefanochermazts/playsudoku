<?php

namespace App\Jobs;

use App\Models\Challenge;
use App\Services\LeaderboardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateLeaderboardJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 60; // 1 minuto timeout
    public int $tries = 2; // Massimo 2 tentativi

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Challenge $challenge
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LeaderboardService $leaderboardService): void
    {
        try {
            Log::info("Updating leaderboard cache for challenge {$this->challenge->id}");

            // Invalida le cache esistenti
            $leaderboardService->clearLeaderboardCache($this->challenge);

            // Pre-popola le cache principali per performance
            $this->preWarmCache($leaderboardService);

            Log::info("Leaderboard cache updated successfully for challenge {$this->challenge->id}");

        } catch (\Exception $e) {
            Log::error("Error updating leaderboard for challenge {$this->challenge->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Pre-popola le cache principali
     */
    private function preWarmCache(LeaderboardService $leaderboardService): void
    {
        // Cache principale della sfida con diversi limiti
        $limits = [50, 100];
        
        foreach ($limits as $limit) {
            $leaderboardService->getChallengeLeaderboard($this->challenge, $limit);
        }

        // Cache specifiche per tipo di sfida
        if ($this->challenge->type === 'daily') {
            $leaderboardService->getDailyLeaderboard($this->challenge->starts_at);
        } elseif ($this->challenge->type === 'weekly') {
            $leaderboardService->getWeeklyLeaderboard($this->challenge->starts_at);
        }

        Log::debug("Pre-warmed cache for challenge {$this->challenge->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UpdateLeaderboardJob failed for challenge {$this->challenge->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
