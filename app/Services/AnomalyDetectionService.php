<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servizio per rilevamento anomalie nei tempi di completamento
 */
class AnomalyDetectionService
{
    /**
     * Soglia Z-score per considerare un tempo anomalo
     */
    private const ANOMALY_THRESHOLD = 3.0;

    /**
     * Numero minimo di campioni per calcolare statistiche affidabili
     */
    private const MIN_SAMPLES = 10;

    /**
     * Analizza i tempi di un tentativo rispetto alla distribuzione della sfida
     */
    public function analyzeAttemptTiming(ChallengeAttempt $attempt): array
    {
        if (!$attempt->completed_at || !$attempt->duration_ms) {
            return [
                'is_anomalous' => false,
                'reason' => 'Tentativo non completato o senza durata',
            ];
        }

        // Ottieni statistiche della sfida
        $stats = $this->getChallengeStatistics($attempt->challenge);
        
        if ($stats['sample_count'] < self::MIN_SAMPLES) {
            return [
                'is_anomalous' => false,
                'reason' => 'Campione insufficiente per analisi statistica',
                'sample_count' => $stats['sample_count'],
            ];
        }

        // Calcola Z-score per il tempo di completamento
        $zScore = $this->calculateZScore(
            $attempt->duration_ms, 
            $stats['mean_duration'], 
            $stats['std_deviation']
        );

        // Determina se è anomalo
        $isAnomalous = abs($zScore) > self::ANOMALY_THRESHOLD;
        
        $result = [
            'is_anomalous' => $isAnomalous,
            'z_score' => $zScore,
            'duration_ms' => $attempt->duration_ms,
            'mean_duration' => $stats['mean_duration'],
            'std_deviation' => $stats['std_deviation'],
            'sample_count' => $stats['sample_count'],
            'percentile' => $this->calculatePercentile($attempt->duration_ms, $stats['all_durations']),
        ];

        if ($isAnomalous) {
            $result['anomaly_type'] = $zScore < -self::ANOMALY_THRESHOLD ? 'too_fast' : 'too_slow';
            $result['reason'] = $zScore < -self::ANOMALY_THRESHOLD 
                ? 'Tempo di completamento sospettosamente veloce'
                : 'Tempo di completamento sospettosamente lento';
        }

        return $result;
    }

    /**
     * Calcola statistiche per una sfida specifica
     */
    public function getChallengeStatistics(Challenge $challenge): array
    {
        $attempts = ChallengeAttempt::where('challenge_id', $challenge->id)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->where('valid', true)
            ->where('duration_ms', '>', 10000) // Minimo 10 secondi
            ->pluck('duration_ms')
            ->toArray();

        if (empty($attempts)) {
            return [
                'sample_count' => 0,
                'mean_duration' => 0,
                'std_deviation' => 0,
                'all_durations' => [],
            ];
        }

        $mean = array_sum($attempts) / count($attempts);
        $variance = $this->calculateVariance($attempts, $mean);
        $stdDev = sqrt($variance);

        return [
            'sample_count' => count($attempts),
            'mean_duration' => $mean,
            'std_deviation' => $stdDev,
            'all_durations' => $attempts,
            'min_duration' => min($attempts),
            'max_duration' => max($attempts),
            'median_duration' => $this->calculateMedian($attempts),
        ];
    }

    /**
     * Analizza tutti i tentativi di una sfida per anomalie
     */
    public function analyzeAllChallengeAttempts(Challenge $challenge): array
    {
        $attempts = ChallengeAttempt::where('challenge_id', $challenge->id)
            ->whereNotNull('completed_at')
            ->whereNotNull('duration_ms')
            ->where('valid', true)
            ->get();

        $anomalies = [];
        $stats = $this->getChallengeStatistics($challenge);

        if ($stats['sample_count'] < self::MIN_SAMPLES) {
            return [
                'total_attempts' => $attempts->count(),
                'anomalies' => [],
                'reason' => 'Campione insufficiente per analisi',
            ];
        }

        foreach ($attempts as $attempt) {
            $analysis = $this->analyzeAttemptTiming($attempt);
            
            if ($analysis['is_anomalous']) {
                $anomalies[] = [
                    'attempt_id' => $attempt->id,
                    'user_id' => $attempt->user_id,
                    'duration_ms' => $attempt->duration_ms,
                    'z_score' => $analysis['z_score'],
                    'anomaly_type' => $analysis['anomaly_type'],
                    'percentile' => $analysis['percentile'],
                ];
            }
        }

        return [
            'total_attempts' => $attempts->count(),
            'anomalies' => $anomalies,
            'anomaly_count' => count($anomalies),
            'anomaly_rate' => count($anomalies) / $attempts->count(),
            'statistics' => $stats,
        ];
    }

    /**
     * Calcola Z-score
     */
    private function calculateZScore(float $value, float $mean, float $stdDev): float
    {
        if ($stdDev === 0.0) {
            return 0.0;
        }
        
        return ($value - $mean) / $stdDev;
    }

    /**
     * Calcola varianza
     */
    private function calculateVariance(array $values, float $mean): float
    {
        if (count($values) <= 1) {
            return 0.0;
        }

        $sumSquaredDifferences = 0.0;
        foreach ($values as $value) {
            $sumSquaredDifferences += pow($value - $mean, 2);
        }

        return $sumSquaredDifferences / (count($values) - 1);
    }

    /**
     * Calcola mediana
     */
    private function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        
        if ($count % 2 === 0) {
            return ($values[$count / 2 - 1] + $values[$count / 2]) / 2;
        } else {
            return $values[intval($count / 2)];
        }
    }

    /**
     * Calcola percentile di un valore
     */
    private function calculatePercentile(float $value, array $allValues): float
    {
        if (empty($allValues)) {
            return 0.0;
        }

        $belowCount = 0;
        foreach ($allValues as $v) {
            if ($v < $value) {
                $belowCount++;
            }
        }

        return ($belowCount / count($allValues)) * 100;
    }

    /**
     * Marca un tentativo come sospetto se presenta anomalie
     */
    public function flagSuspiciousAttempt(ChallengeAttempt $attempt, array $analysis): void
    {
        if (!$analysis['is_anomalous']) {
            return;
        }

        $notes = sprintf(
            'Tempo anomalo rilevato: %s (Z-score: %.2f, Percentile: %.1f%%)',
            $analysis['anomaly_type'] === 'too_fast' ? 'Troppo veloce' : 'Troppo lento',
            $analysis['z_score'],
            $analysis['percentile']
        );

        // Se esistono i campi nella migrazione, li aggiorna
        try {
            $updateData = ['flagged_for_review' => true];
            
            // Prova ad aggiornare con i nuovi campi se esistono
            if (DB::getSchemaBuilder()->hasColumn('challenge_attempts', 'validation_notes')) {
                $updateData['validation_notes'] = $notes;
            }

            $attempt->update($updateData);

            Log::warning('Suspicious attempt flagged for timing anomaly', [
                'attempt_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'challenge_id' => $attempt->challenge_id,
                'z_score' => $analysis['z_score'],
                'anomaly_type' => $analysis['anomaly_type'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error flagging suspicious attempt', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


