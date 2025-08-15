<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Services\AnomalyDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job per analisi anomalie di timing su una sfida
 */
class AnalyzeTimingAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Challenge $challenge,
        public ?ChallengeAttempt $specificAttempt = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnomalyDetectionService $anomalyService): void
    {
        try {
            Log::info('Starting timing anomaly analysis', [
                'challenge_id' => $this->challenge->id,
                'challenge_type' => $this->challenge->type,
                'specific_attempt' => $this->specificAttempt?->id,
            ]);

            if ($this->specificAttempt) {
                // Analizza un tentativo specifico
                $this->analyzeSpecificAttempt($anomalyService);
            } else {
                // Analizza tutta la sfida
                $this->analyzeAllAttempts($anomalyService);
            }

        } catch (\Exception $e) {
            Log::error('Error during timing anomaly analysis', [
                'challenge_id' => $this->challenge->id,
                'specific_attempt' => $this->specificAttempt?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Analizza un tentativo specifico
     */
    private function analyzeSpecificAttempt(AnomalyDetectionService $anomalyService): void
    {
        $analysis = $anomalyService->analyzeAttemptTiming($this->specificAttempt);
        
        if ($analysis['is_anomalous']) {
            $anomalyService->flagSuspiciousAttempt($this->specificAttempt, $analysis);
            
            Log::warning('Timing anomaly detected', [
                'attempt_id' => $this->specificAttempt->id,
                'user_id' => $this->specificAttempt->user_id,
                'z_score' => $analysis['z_score'],
                'anomaly_type' => $analysis['anomaly_type'],
                'duration_ms' => $analysis['duration_ms'],
                'percentile' => $analysis['percentile'],
            ]);
        } else {
            Log::info('No timing anomaly detected', [
                'attempt_id' => $this->specificAttempt->id,
                'z_score' => $analysis['z_score'] ?? null,
                'reason' => $analysis['reason'] ?? 'Normal timing',
            ]);
        }
    }

    /**
     * Analizza tutti i tentativi della sfida
     */
    private function analyzeAllAttempts(AnomalyDetectionService $anomalyService): void
    {
        $analysis = $anomalyService->analyzeAllChallengeAttempts($this->challenge);
        
        Log::info('Challenge timing analysis completed', [
            'challenge_id' => $this->challenge->id,
            'total_attempts' => $analysis['total_attempts'],
            'anomaly_count' => $analysis['anomaly_count'] ?? 0,
            'anomaly_rate' => $analysis['anomaly_rate'] ?? 0,
        ]);

        // Marca i tentativi anomali se trovati
        if (!empty($analysis['anomalies'])) {
            foreach ($analysis['anomalies'] as $anomaly) {
                $attempt = ChallengeAttempt::find($anomaly['attempt_id']);
                if ($attempt) {
                    $anomalyService->flagSuspiciousAttempt($attempt, [
                        'is_anomalous' => true,
                        'z_score' => $anomaly['z_score'],
                        'anomaly_type' => $anomaly['anomaly_type'],
                        'percentile' => $anomaly['percentile'],
                    ]);
                }
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeTimingAnomaliesJob failed', [
            'challenge_id' => $this->challenge->id,
            'specific_attempt' => $this->specificAttempt?->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}


