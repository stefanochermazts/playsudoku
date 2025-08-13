<?php

namespace App\Console\Commands;

use App\Domain\Sudoku\Contracts\GeneratorInterface;
use App\Domain\Sudoku\Contracts\DifficultyRaterInterface;
use Illuminate\Console\Command;

class TestAdminGeneratorCommand extends Command
{
    protected $signature = 'test:admin-generator {--seed=1000 : Seed per la generazione} {--difficulty=easy : Difficoltà del puzzle}';

    protected $description = 'Testa la stessa logica dell\'admin panel';

    public function handle()
    {
        $seed = (int) $this->option('seed');
        $difficulty = $this->option('difficulty');
        
        $this->info("🔧 Testando logica IDENTICA all'admin panel");
        $this->info("🌱 Seed: {$seed}");
        $this->info("🎯 Difficoltà: {$difficulty}");
        $this->line('');
        
        try {
            $generator = app(GeneratorInterface::class);
            $difficultyRater = app(DifficultyRaterInterface::class);
            
            $this->info("📝 Step 1: generateCompleteGrid...");
            $start = microtime(true);
            $completeGrid = $generator->generateCompleteGrid($seed);
            $time1 = round((microtime(true) - $start) * 1000, 2);
            $this->line("✅ Completato in {$time1}ms");
            
            $this->info("📝 Step 2: generatePuzzleWithDifficulty...");
            $start = microtime(true);
            $puzzleGrid = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
            $time2 = round((microtime(true) - $start) * 1000, 2);
            $this->line("✅ Completato in {$time2}ms");
            
            $this->info("📝 Step 3: rateDifficulty...");
            $start = microtime(true);
            $actualDifficulty = $difficultyRater->rateDifficulty($puzzleGrid);
            $time3 = round((microtime(true) - $start) * 1000, 2);
            $this->line("✅ Completato in {$time3}ms - Difficoltà: {$actualDifficulty}");
            
            $this->line('');
            $this->info("🎯 Test completato con successo!");
            $this->line("⏱️ Tempo totale: " . ($time1 + $time2 + $time3) . "ms");
            $this->line("🔢 Puzzle cells: " . (81 - $puzzleGrid->countEmptyCells()) . "/81");
            $this->line("🔢 Complete grid cells: " . (81 - $completeGrid->countEmptyCells()) . "/81");
            
        } catch (\Exception $e) {
            $this->error("💥 Errore:");
            $this->error("📄 Messaggio: " . $e->getMessage());
            $this->error("📁 File: " . $e->getFile() . ":" . $e->getLine());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
        }
        
        return 0;
    }
}
