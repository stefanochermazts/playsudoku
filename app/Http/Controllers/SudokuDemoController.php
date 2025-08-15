<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Sudoku\Contracts\GeneratorInterface;
use Illuminate\Http\Request;

class SudokuDemoController extends Controller
{
    public function index()
    {
        // Configura SEO meta tags per training
        $metaService = app(\App\Services\MetaService::class);
        $metaService->setTraining();
        
        return view('sudoku.demo', ['metaService' => $metaService]);
    }

    public function play()
    {
        // Genera un puzzle di esempio usando il service container
        $generator = app(GeneratorInterface::class);
        
        $seed = random_int(1000, 999999);
        $puzzle = $generator->generatePuzzleWithDifficulty($seed, 'medium');
        
        return view('sudoku.play', [
            'initialGrid' => $puzzle->toArray(),
            'seed' => $seed,
        ]);
    }

    /**
     * Pagina per analizzare puzzle importati
     */
    public function analyzer()
    {
        // Configura SEO meta tags per analyzer
        $metaService = app(\App\Services\MetaService::class);
        $metaService->setAnalyzer();
        
        return view('sudoku.analyzer', ['metaService' => $metaService]);
    }
}
