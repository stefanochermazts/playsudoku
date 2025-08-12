<?php

namespace App\Jobs;

use App\Services\ChallengeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePuzzleBatchJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300; // 5 minuti timeout
    public int $tries = 2; // Massimo 2 tentativi

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $puzzleSpecs, // Array di [seed, difficulty]
        public string $batchId = '',
    ) {
        $this->batchId = $batchId ?: 'batch_' . time();
    }

    /**
     * Execute the job.
     */
    public function handle(ChallengeService $challengeService): void
    {
        try {
            Log::info("Starting puzzle batch generation", [
                'batch_id' => $this->batchId,
                'puzzle_count' => count($this->puzzleSpecs)
            ]);

            $generated = 0;
            $failed = 0;

            foreach ($this->puzzleSpecs as $spec) {
                try {
                    $seed = $spec['seed'];
                    $difficulty = $spec['difficulty'];

                    // Genera o ottieni il puzzle
                    $puzzle = $challengeService->getOrCreatePuzzle($seed, $difficulty);
                    $generated++;

                    Log::debug("Generated puzzle", [
                        'batch_id' => $this->batchId,
                        'puzzle_id' => $puzzle->id,
                        'seed' => $seed,
                        'difficulty' => $difficulty
                    ]);

                } catch (\Exception $e) {
                    $failed++;
                    Log::warning("Failed to generate puzzle", [
                        'batch_id' => $this->batchId,
                        'spec' => $spec,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Puzzle batch generation completed", [
                'batch_id' => $this->batchId,
                'generated' => $generated,
                'failed' => $failed,
                'total' => count($this->puzzleSpecs)
            ]);

        } catch (\Exception $e) {
            Log::error("Error in puzzle batch generation", [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Crea un job per generare puzzle per i prossimi giorni
     */
    public static function forDailyPuzzles(int $days = 7): self
    {
        $specs = [];
        $difficulties = ['easy', 'normal', 'hard', 'expert', 'crazy'];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->addDays($i);
            $seed = abs(crc32("daily_" . $date->toDateString())) % 999999 + 1000;
            
            // Distribuzione delle difficoltà per i puzzle daily
            $difficultyIndex = $i % count($difficulties);
            $difficulty = $difficulties[$difficultyIndex];

            $specs[] = [
                'seed' => $seed,
                'difficulty' => $difficulty,
                'type' => 'daily',
                'date' => $date->toDateString(),
            ];
        }

        return new self($specs, 'daily_batch_' . now()->format('Y_m_d'));
    }

    /**
     * Crea un job per generare puzzle per le prossime settimane
     */
    public static function forWeeklyPuzzles(int $weeks = 4): self
    {
        $specs = [];
        $difficulties = ['hard', 'expert', 'crazy'];

        for ($i = 0; $i < $weeks; $i++) {
            $date = now()->addWeeks($i)->startOfWeek();
            $year = $date->year;
            $week = $date->weekOfYear;
            $seed = abs(crc32("weekly_{$year}_w{$week}")) % 999999 + 1000;
            
            // Le sfide weekly sono sempre più difficili
            $difficultyIndex = $i % count($difficulties);
            $difficulty = $difficulties[$difficultyIndex];

            $specs[] = [
                'seed' => $seed,
                'difficulty' => $difficulty,
                'type' => 'weekly',
                'week' => $date->toDateString(),
            ];
        }

        return new self($specs, 'weekly_batch_' . now()->format('Y_m_d'));
    }

    /**
     * Crea un job per generare puzzle custom con specifiche particolari
     */
    public static function forCustomPuzzles(array $customSpecs): self
    {
        return new self($customSpecs, 'custom_batch_' . time());
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GeneratePuzzleBatchJob failed", [
            'batch_id' => $this->batchId,
            'puzzle_specs_count' => count($this->puzzleSpecs),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
