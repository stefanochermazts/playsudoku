<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Sudoku\Contracts\GeneratorInterface;
use Illuminate\Http\Request;

class SudokuDemoController extends Controller
{
    public function index()
    {
        return view('sudoku.demo');
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
}
