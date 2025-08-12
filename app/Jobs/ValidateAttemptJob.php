<?php

namespace App\Jobs;

use App\Models\ChallengeAttempt;
use App\Services\ResultService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateAttemptJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 120; // 2 minuti timeout
    public int $tries = 3; // Massimo 3 tentativi

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ChallengeAttempt $attempt
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ResultService $resultService): void
    {
        try {
            Log::info("Validating attempt {$this->attempt->id} for challenge {$this->attempt->challenge_id}");

            // Verifica l'integrità del tentativo
            $integrity = $resultService->verifyAttemptIntegrity($this->attempt);
            
            if (!$integrity['is_valid']) {
                Log::warning("Attempt {$this->attempt->id} failed integrity check", [
                    'issues' => $integrity['issues']
                ]);

                // Marca il tentativo come non valido
                $this->attempt->update(['valid' => false]);
                return;
            }

            // Se il tentativo ha mosse registrate, ricostruisce e valida la sequenza
            if ($this->attempt->moves()->count() > 0) {
                $moveLog = $resultService->reconstructMoveLog($this->attempt);
                $validation = $resultService->validateMoveSequence(
                    $this->attempt->challenge->puzzle->toGrid(),
                    $moveLog
                );

                if (!$validation['is_valid']) {
                    Log::warning("Attempt {$this->attempt->id} failed move sequence validation", [
                        'errors' => $validation['errors']
                    ]);

                    // Marca il tentativo come non valido
                    $this->attempt->update(['valid' => false]);
                    return;
                }
            }

            // Validazione anti-cheat: verifica tempi anomali
            $this->validateTiming();

            Log::info("Attempt {$this->attempt->id} validation completed successfully");

        } catch (\Exception $e) {
            Log::error("Error validating attempt {$this->attempt->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Non marcare come non valido in caso di errore del sistema
            throw $e;
        }
    }

    /**
     * Valida i tempi per rilevare possibili cheating
     */
    private function validateTiming(): void
    {
        $difficulty = $this->attempt->challenge->puzzle->difficulty;
        $duration = $this->attempt->duration_ms;

        // Definisci tempi minimi realistici per difficoltà
        $minTimes = [
            'easy' => 30000,    // 30 secondi
            'normal' => 60000,  // 1 minuto
            'hard' => 120000,   // 2 minuti
            'expert' => 180000, // 3 minuti
            'crazy' => 300000,  // 5 minuti
        ];

        $minTime = $minTimes[$difficulty] ?? 60000;

        if ($duration < $minTime) {
            Log::warning("Attempt {$this->attempt->id} has suspiciously fast time", [
                'duration_ms' => $duration,
                'min_expected' => $minTime,
                'difficulty' => $difficulty
            ]);

            // Marca come sospetto ma non invalido automaticamente
            // Un admin può revisionare manualmente
        }

        // Verifica anche tempi troppo lunghi (possibile pausa o AFK)
        $maxTime = 24 * 60 * 60 * 1000; // 24 ore
        if ($duration > $maxTime) {
            Log::warning("Attempt {$this->attempt->id} has unusually long time", [
                'duration_ms' => $duration,
                'max_reasonable' => $maxTime
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ValidateAttemptJob failed for attempt {$this->attempt->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
