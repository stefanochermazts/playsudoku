<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use Illuminate\Http\Request;

class SudokuDemoController extends Controller
{
    public function index()
    {
        return view('sudoku.demo');
    }

    public function play()
    {
        // Genera un puzzle di esempio
        $validator = new Validator();
        $generator = new Generator($validator);
        
        $seed = random_int(1000, 999999);
        $puzzle = $generator->generatePuzzleWithDifficulty($seed, 'normal');
        
        return view('sudoku.play', [
            'initialGrid' => $puzzle->toArray(),
            'seed' => $seed,
        ]);
    }
}
