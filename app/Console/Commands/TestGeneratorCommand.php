<?php

namespace App\Console\Commands;

use App\Domain\Sudoku\Contracts\GeneratorInterface;
use Illuminate\Console\Command;

class TestGeneratorCommand extends Command
{
    protected $signature = 'test:generator {--seed=123 : Seed per la generazione} {--timeout=10 : Timeout in secondi} {--count=1 : Numero di puzzle da generare} {--difficulty=easy : Difficoltà del puzzle}';

    protected $description = 'Testa il generatore Sudoku con debug dettagliato';

    public function handle()
    {
        $seed = (int) $this->option('seed');
        $timeout = (int) $this->option('timeout');
        $count = (int) $this->option('count');
        $difficulty = $this->option('difficulty');
        
        $this->info("🔧 Testando generatore Sudoku");
        $this->info("🌱 Seed base: {$seed}");
        $this->info("📊 Puzzle da generare: {$count}");
        $this->info("🎯 Difficoltà: {$difficulty}");
        $this->info("⏱️ Timeout impostato: {$timeout} secondi");
        $this->line('');
        
        // Abilita logging dettagliato
        \Log::info("=== GENERATOR TEST START ===", ['seed' => $seed]);
        
        try {
            $generator = app(GeneratorInterface::class);
            $generated = 0;
            $failed = 0;
            
            for ($i = 0; $i < $count; $i++) {
                $currentSeed = $seed + $i;
                $testNum = $i + 1;
                $this->info("📝 Test {$testNum}/{$count}: Generazione con seed {$currentSeed}...");
                
                $startTime = microtime(true);
                $memoryBefore = memory_get_usage(true);
                
                // Test generazione puzzle completo
                $puzzle = $generator->generatePuzzleWithDifficulty($currentSeed, $difficulty);
            
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                $memoryAfter = memory_get_usage(true);
                $memoryUsed = $memoryAfter - $memoryBefore;
                
                if ($puzzle) {
                    $filledCells = 81 - $puzzle->countEmptyCells();
                    $this->line("✅ Successo! Tempo: {$elapsed}ms, Celle: {$filledCells}/81, Memoria: " . $this->formatBytes($memoryUsed));
                    $generated++;
                } else {
                    $this->line("❌ Fallita! Tempo: {$elapsed}ms");
                    $failed++;
                }
            }
            
            $this->line('');
            $this->info("📊 Risultati finali:");
            $this->line("✅ Generati con successo: {$generated}/{$count}");
            $this->line("❌ Falliti: {$failed}/{$count}");
            
            if ($generated > 0) {
                $this->line("🎯 Successo! Il generatore funziona correttamente.");
            } else {
                $this->error("💥 Tutti i tentativi sono falliti!");
            }
            
        } catch (\Exception $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            $this->error("💥 Errore durante generazione:");
            $this->error("📄 Messaggio: " . $e->getMessage());
            $this->error("📁 File: " . $e->getFile() . ":" . $e->getLine());
            $this->line("⏱️ Tempo prima errore: {$elapsed}ms");
        }
        
        \Log::info("=== GENERATOR TEST END ===");
        
        $this->line('');
        $this->info("📊 Controlla i log in storage/logs/laravel.log per dettagli completi");
        
        return 0;
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}