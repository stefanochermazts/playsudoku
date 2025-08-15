<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Puzzle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomepageStatsService
{
    /**
     * Get all homepage statistics with caching.
     */
    public function getStats(): array
    {
        return Cache::remember('homepage_stats', 300, function () { // 5 minuti cache
            return [
                'total_users' => $this->getTotalUsers(),
                'total_challenges' => $this->getTotalChallenges(),
                'total_attempts' => $this->getTotalAttempts(),
                'total_puzzles_solved' => $this->getTotalPuzzlesSolved(),
                'avg_completion_time' => $this->getAverageCompletionTime(),
                'active_users_today' => $this->getActiveUsersToday(),
                'challenges_completed_today' => $this->getChallengesCompletedToday(),
                'featured_stats' => $this->getFeaturedStats(),
            ];
        });
    }

    /**
     * Get total registered users count.
     */
    private function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Get total challenges created.
     */
    private function getTotalChallenges(): int
    {
        return Challenge::count();
    }

    /**
     * Get total challenge attempts.
     */
    private function getTotalAttempts(): int
    {
        return ChallengeAttempt::count();
    }

    /**
     * Get total puzzles solved (completed attempts).
     */
    private function getTotalPuzzlesSolved(): int
    {
        return ChallengeAttempt::whereNotNull('completed_at')->count();
    }

    /**
     * Get average completion time in minutes.
     */
    private function getAverageCompletionTime(): ?float
    {
        $avgMilliseconds = ChallengeAttempt::whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->where('duration_ms', '>', 0)
            ->avg('duration_ms');

        return $avgMilliseconds ? round($avgMilliseconds / 60000, 1) : null; // ms to minutes
    }

    /**
     * Get active users today (using created_at and updated_at as proxy).
     */
    private function getActiveUsersToday(): int
    {
        return User::where('updated_at', '>=', now()->startOfDay())
            ->orWhere('created_at', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * Get challenges completed today.
     */
    private function getChallengesCompletedToday(): int
    {
        return ChallengeAttempt::whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * Get featured statistics for homepage highlights.
     */
    private function getFeaturedStats(): array
    {
        return [
            'daily_challenges_available' => Challenge::where('type', 'daily')
                ->where('status', 'active')
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->count(),
            'weekly_challenges_available' => Challenge::where('type', 'weekly')
                ->where('status', 'active')
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->count(),
            'difficulty_distribution' => $this->getDifficultyDistribution(),
            'top_completion_time' => $this->getTopCompletionTime(),
            'puzzles_by_difficulty' => $this->getPuzzlesByDifficulty(),
        ];
    }

    /**
     * Get difficulty distribution for completed attempts.
     */
    private function getDifficultyDistribution(): array
    {
        $distribution = ChallengeAttempt::select('puzzles.difficulty', DB::raw('count(*) as count'))
            ->join('challenges', 'challenge_attempts.challenge_id', '=', 'challenges.id')
            ->join('puzzles', 'challenges.puzzle_id', '=', 'puzzles.id')
            ->whereNotNull('challenge_attempts.completed_at')
            ->groupBy('puzzles.difficulty')
            ->pluck('count', 'difficulty')
            ->toArray();

        return [
            'easy' => $distribution['easy'] ?? 0,
            'medium' => $distribution['medium'] ?? 0,
            'hard' => $distribution['hard'] ?? 0,
            'expert' => $distribution['expert'] ?? 0,
            'insane' => $distribution['insane'] ?? 0,
        ];
    }

    /**
     * Get best completion time today.
     */
    private function getTopCompletionTime(): ?array
    {
        $bestAttempt = ChallengeAttempt::select('challenge_attempts.*', 'users.name', 'puzzles.difficulty')
            ->join('users', 'challenge_attempts.user_id', '=', 'users.id')
            ->join('challenges', 'challenge_attempts.challenge_id', '=', 'challenges.id')
            ->join('puzzles', 'challenges.puzzle_id', '=', 'puzzles.id')
            ->whereNotNull('challenge_attempts.completed_at')
            ->where('challenge_attempts.completed_at', '>=', now()->startOfDay())
            ->where('challenge_attempts.duration_ms', '>', 0)
            ->orderBy('challenge_attempts.duration_ms')
            ->first();

        if (!$bestAttempt) {
            return null;
        }

        return [
            'user_name' => $bestAttempt->name,
            'time_minutes' => round($bestAttempt->duration_ms / 60000, 1),
            'difficulty' => $bestAttempt->difficulty,
        ];
    }

    /**
     * Get puzzles count by difficulty.
     */
    private function getPuzzlesByDifficulty(): array
    {
        return Puzzle::select('difficulty', DB::raw('count(*) as count'))
            ->groupBy('difficulty')
            ->pluck('count', 'difficulty')
            ->toArray();
    }

    /**
     * Get testimonials for social proof.
     */
    public function getTestimonials(): array
    {
        // Per ora testimonials hardcoded, in futuro potrebbero venire dal database
        return [
            [
                'name' => 'Marco R.',
                'text_key' => 'homepage.testimonials.marco',
                'rating' => 5,
                'location' => 'Milano, IT'
            ],
            [
                'name' => 'Sarah J.',
                'text_key' => 'homepage.testimonials.sarah',
                'rating' => 5,
                'location' => 'New York, US'
            ],
            [
                'name' => 'Giovanni P.',
                'text_key' => 'homepage.testimonials.giovanni',
                'rating' => 4,
                'location' => 'Roma, IT'
            ],
        ];
    }

    /**
     * Get real-time statistics for live counters.
     */
    public function getLiveStats(): array
    {
        return Cache::remember('homepage_live_stats', 60, function () { // 1 minuto cache
            return [
                'users_online' => $this->getUsersOnline(),
                'games_in_progress' => $this->getGamesInProgress(),
                'completed_today' => $this->getChallengesCompletedToday(),
            ];
        });
    }

    /**
     * Get approximate users online (using updated_at as proxy for last 15 minutes).
     */
    private function getUsersOnline(): int
    {
        return User::where('updated_at', '>=', now()->subMinutes(15))->count();
    }

    /**
     * Get games currently in progress.
     */
    private function getGamesInProgress(): int
    {
        return ChallengeAttempt::whereNull('completed_at')
            ->where('started_at', '>=', now()->subHours(2)) // Started in last 2 hours
            ->count();
    }

    /**
     * Format large numbers for display.
     */
    public function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        
        return (string) $number;
    }

    /**
     * Get percentage growth compared to last period.
     */
    public function getGrowthPercentage(string $metric, string $period = 'week'): ?float
    {
        $days = $period === 'week' ? 7 : 30;
        
        $current = $this->getMetricForPeriod($metric, 0, $days);
        $previous = $this->getMetricForPeriod($metric, $days, $days);
        
        if ($previous === 0) {
            return null;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get metric value for a specific period.
     */
    private function getMetricForPeriod(string $metric, int $daysAgo, int $period): int
    {
        $startDate = now()->subDays($daysAgo + $period);
        $endDate = now()->subDays($daysAgo);

        return match ($metric) {
            'users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'attempts' => ChallengeAttempt::whereBetween('started_at', [$startDate, $endDate])->count(),
            'completed' => ChallengeAttempt::whereNotNull('completed_at')
                ->whereBetween('completed_at', [$startDate, $endDate])->count(),
            default => 0,
        };
    }
}
