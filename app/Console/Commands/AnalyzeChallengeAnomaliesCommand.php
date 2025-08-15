<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Jobs\AnalyzeTimingAnomaliesJob;
use Illuminate\Console\Command;

/**
 * Comando per analizzare anomalie di timing in una o più sfide
 */
class AnalyzeChallengeAnomaliesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sudoku:analyze-anomalies
                           {challenge? : ID della sfida specifica da analizzare}
                           {--type= : Tipo di sfida da analizzare (daily, weekly, custom)}
                           {--recent= : Analizza solo le ultime N sfide (default: 10)}
                           {--sync : Esegui l\'analisi in modo sincrono invece che in coda}';

    /**
     * The console command description.
     */
    protected $description = 'Analizza anomalie di timing nelle sfide per rilevare possibili comportamenti sospetti';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $challengeId = $this->argument('challenge');
        $type = $this->option('type');
        $recent = (int) $this->option('recent') ?: 10;
        $sync = $this->option('sync');

        if ($challengeId) {
            // Analizza una sfida specifica
            return $this->analyzeSpecificChallenge((int) $challengeId, $sync);
        } else {
            // Analizza più sfide
            return $this->analyzeMultipleChallenges($type, $recent, $sync);
        }
    }

    /**
     * Analizza una sfida specifica
     */
    private function analyzeSpecificChallenge(int $challengeId, bool $sync): int
    {
        $challenge = Challenge::find($challengeId);
        
        if (!$challenge) {
            $this->error("Sfida con ID {$challengeId} non trovata.");
            return 1;
        }

        $this->info("Analizzando sfida: {$challenge->title} (ID: {$challenge->id})");

        if ($sync) {
            $service = app(\App\Services\AnomalyDetectionService::class);
            $analysis = $service->analyzeAllChallengeAttempts($challenge);
            
            $this->displayAnalysisResults($challenge, $analysis);
        } else {
            AnalyzeTimingAnomaliesJob::dispatch($challenge);
            $this->info('Job di analisi schedulato. Controlla i log per i risultati.');
        }

        return 0;
    }

    /**
     * Analizza più sfide
     */
    private function analyzeMultipleChallenges(?string $type, int $recent, bool $sync): int
    {
        $query = Challenge::whereNotNull('ends_at')
            ->orderBy('created_at', 'desc')
            ->limit($recent);

        if ($type) {
            $query->where('type', $type);
        }

        $challenges = $query->get();

        if ($challenges->isEmpty()) {
            $this->error('Nessuna sfida trovata con i criteri specificati.');
            return 1;
        }

        $this->info("Analizzando {$challenges->count()} sfide...");

        $progressBar = $this->output->createProgressBar($challenges->count());
        $progressBar->start();

        foreach ($challenges as $challenge) {
            if ($sync) {
                $service = app(\App\Services\AnomalyDetectionService::class);
                $analysis = $service->analyzeAllChallengeAttempts($challenge);
                
                if (!empty($analysis['anomalies'])) {
                    $this->newLine();
                    $this->warn("Anomalie trovate in: {$challenge->title} (ID: {$challenge->id})");
                    $this->displayAnalysisResults($challenge, $analysis);
                }
            } else {
                AnalyzeTimingAnomaliesJob::dispatch($challenge);
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if (!$sync) {
            $this->info('Job di analisi schedulati. Controlla i log per i risultati.');
        }

        return 0;
    }

    /**
     * Mostra i risultati dell'analisi
     */
    private function displayAnalysisResults(Challenge $challenge, array $analysis): void
    {
        $this->newLine();
        $this->line("=== Risultati per: {$challenge->title} ===");
        $this->line("Tentativi totali: {$analysis['total_attempts']}");
        
        if (isset($analysis['anomaly_count'])) {
            $this->line("Anomalie rilevate: {$analysis['anomaly_count']}");
            $this->line("Tasso di anomalie: " . round(($analysis['anomaly_rate'] ?? 0) * 100, 2) . "%");
        }

        if (!empty($analysis['statistics'])) {
            $stats = $analysis['statistics'];
            $this->line("Tempo medio: " . round($stats['mean_duration'] / 1000, 1) . "s");
            $this->line("Deviazione standard: " . round($stats['std_deviation'] / 1000, 1) . "s");
            $this->line("Tempo minimo: " . round($stats['min_duration'] / 1000, 1) . "s");
            $this->line("Tempo massimo: " . round($stats['max_duration'] / 1000, 1) . "s");
        }

        if (!empty($analysis['anomalies'])) {
            $this->newLine();
            $this->line("Dettagli anomalie:");
            
            $headers = ['Attempt ID', 'User ID', 'Durata (s)', 'Z-Score', 'Tipo', 'Percentile'];
            $rows = [];
            
            foreach ($analysis['anomalies'] as $anomaly) {
                $rows[] = [
                    $anomaly['attempt_id'],
                    $anomaly['user_id'],
                    round($anomaly['duration_ms'] / 1000, 1),
                    round($anomaly['z_score'], 2),
                    $anomaly['anomaly_type'] === 'too_fast' ? 'Veloce' : 'Lento',
                    round($anomaly['percentile'], 1) . '%',
                ];
            }
            
            $this->table($headers, $rows);
        }
        
        $this->newLine();
    }
}


