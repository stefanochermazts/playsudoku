<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Sudoku\Contracts\SolverInterface;
use App\Domain\Sudoku\Contracts\DifficultyRaterInterface;
use App\Models\PublicPuzzle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job asincrono per processare puzzle pubblici del Solver AI.
 * 
 * Si occupa di:
 * - Risolvere il puzzle con il solver
 * - Calcolare la difficoltà
 * - Generare metadati SEO
 * - Invalidare cache
 * - Notificare sitemap update
 */
class ProcessPublicPuzzleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The puzzle to process.
     */
    public PublicPuzzle $puzzle;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(PublicPuzzle $puzzle)
    {
        $this->puzzle = $puzzle;
        $this->onQueue('solver'); // Use dedicated queue for solver jobs
    }

    /**
     * Execute the job.
     */
    public function handle(
        SolverInterface $solver,
        DifficultyRaterInterface $difficultyRater
    ): void {
        try {
            Log::info('Starting public puzzle processing', [
                'puzzle_id' => $this->puzzle->id,
                'hash' => $this->puzzle->hash,
            ]);

            // Verifica che il puzzle non sia già stato processato
            if ($this->puzzle->status === 'processed') {
                Log::info('Puzzle already processed, skipping', [
                    'puzzle_id' => $this->puzzle->id
                ]);
                return;
            }

            // Converte la griglia in oggetto del dominio
            $grid = $this->puzzle->toGrid();
            
            // Calcola difficoltà
            $difficulty = $difficultyRater->rateDifficulty($grid);
            
            // Risolve il puzzle
            $startTime = microtime(true);
            $result = $solver->solve($grid);
            $endTime = microtime(true);
            
            $solvingTime = round(($endTime - $startTime) * 1000); // in milliseconds

            // Aggiorna il modello con i risultati
            $this->puzzle->update([
                'difficulty' => $difficulty,
                'solving_time_ms' => $solvingTime,
            ]);

            // Marca come processato con i risultati del solver
            $this->puzzle->markAsProcessed([
                'grid' => $result['grid'],
                'steps' => $result['steps'] ?? [],
                'techniques' => $result['techniques'] ?? [],
                'solving_time_ms' => $solvingTime,
            ]);

            // Genera metadati SEO automaticamente
            $this->puzzle->generateSeoMetadata();

            // Invalida cache correlate
            $this->invalidateRelatedCache();

            // TODO: Notifica sitemap per update (implementare SitemapManagerService)
            // $this->notifySitemapUpdate();

            Log::info('Public puzzle processing completed successfully', [
                'puzzle_id' => $this->puzzle->id,
                'hash' => $this->puzzle->hash,
                'difficulty' => $difficulty,
                'is_solvable' => $result['grid'] !== null,
                'solving_time_ms' => $solvingTime,
                'techniques_used' => count($result['techniques'] ?? []),
            ]);

        } catch (Exception $e) {
            Log::error('Error processing public puzzle', [
                'puzzle_id' => $this->puzzle->id,
                'hash' => $this->puzzle->hash,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Marca il puzzle come fallito
            $this->puzzle->update([
                'status' => 'failed',
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Public puzzle processing job failed permanently', [
            'puzzle_id' => $this->puzzle->id,
            'hash' => $this->puzzle->hash,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Aggiorna lo status del puzzle a failed
        $this->puzzle->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Invalida le cache correlate
     */
    private function invalidateRelatedCache(): void
    {
        try {
            // Cache delle statistiche pubbliche
            Cache::forget('public_solver_stats');
            
            // Cache dei puzzle popolari
            Cache::forget('public_solver_popular');
            
            // Cache delle statistiche per difficoltà se applicabile
            if ($this->puzzle->difficulty) {
                Cache::forget('public_solver_stats_' . $this->puzzle->difficulty);
            }

            Log::debug('Invalidated related cache for processed puzzle', [
                'puzzle_id' => $this->puzzle->id
            ]);

        } catch (Exception $e) {
            // Non bloccare il job per errori di cache
            Log::warning('Failed to invalidate cache for processed puzzle', [
                'puzzle_id' => $this->puzzle->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notifica il sistema sitemap per l'aggiornamento
     * TODO: Implementare quando avremo SitemapManagerService
     */
    private function notifySitemapUpdate(): void
    {
        // Per ora solo log, in futuro:
        // - Dispatch job per update sitemap
        // - Ping Google/Bing con nuovo URL
        // - Aggiornare index sitemap

        Log::debug('Sitemap update notification (placeholder)', [
            'puzzle_id' => $this->puzzle->id,
            'url' => $this->puzzle->getPublicUrl(),
        ]);
    }

    /**
     * Determina il delay prima del retry
     */
    public function backoff(): array
    {
        return [60, 180, 300]; // 1min, 3min, 5min
    }
}
