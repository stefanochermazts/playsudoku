<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChallengeAttempt;
use App\Services\MoveValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job per validazione anti-cheat delle mosse di un tentativo
 */
class ValidateAttemptMovesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ChallengeAttempt $attempt,
        public bool $fullValidation = true
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MoveValidationService $validationService): void
    {
        try {
            Log::info('Starting move validation', [
                'attempt_id' => $this->attempt->id,
                'user_id' => $this->attempt->user_id,
                'challenge_id' => $this->attempt->challenge_id,
                'full_validation' => $this->fullValidation,
            ]);

            // Esegui validazione completa o sampling
            $validationPassed = $this->fullValidation 
                ? $validationService->validateAttemptMoves($this->attempt)
                : $validationService->validateAttemptSampling($this->attempt);

            // Aggiorna il tentativo con i risultati
            $this->attempt->update([
                'move_validation_passed' => $validationPassed,
                'validated_at' => now(),
                'validation_notes' => $validationPassed 
                    ? 'Validazione automatica superata' 
                    : 'Validazione fallita - possibile anomalia',
                'flagged_for_review' => !$validationPassed,
            ]);

            if (!$validationPassed) {
                Log::warning('Move validation failed', [
                    'attempt_id' => $this->attempt->id,
                    'user_id' => $this->attempt->user_id,
                ]);

                // Qui potresti aggiungere notifiche agli admin
                // event(new SuspiciousAttemptDetected($this->attempt));
            }

            Log::info('Move validation completed', [
                'attempt_id' => $this->attempt->id,
                'validation_passed' => $validationPassed,
            ]);

        } catch (\Exception $e) {
            Log::error('Error during move validation', [
                'attempt_id' => $this->attempt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Marca come necessario revisione manuale in caso di errore
            $this->attempt->update([
                'move_validation_passed' => null,
                'validated_at' => now(),
                'validation_notes' => 'Errore durante validazione: ' . $e->getMessage(),
                'flagged_for_review' => true,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ValidateAttemptMovesJob failed', [
            'attempt_id' => $this->attempt->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}


