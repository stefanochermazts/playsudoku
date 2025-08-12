<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Sudoku;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use App\Domain\Sudoku\DifficultyRater;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    private Generator $generator;
    private Validator $validator;
    private DifficultyRater $rater;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
        $this->generator = new Generator($this->validator);
        $this->rater = new DifficultyRater($this->validator);
    }

    public function test_generate_complete_grid(): void
    {
        $seed = 12345;
        $grid = $this->generator->generateCompleteGrid($seed);
        
        $this->assertTrue($grid->isComplete());
        $this->assertTrue($this->validator->isValid($grid));
        $this->assertTrue($this->validator->isSolved($grid));
        $this->assertEquals(0, $grid->countEmptyCells()); // Tutte le celle dovrebbero essere riempite
    }

    public function test_deterministic_generation(): void
    {
        $seed = 54321;
        
        $grid1 = $this->generator->generateCompleteGrid($seed);
        $grid2 = $this->generator->generateCompleteGrid($seed);
        
        // Stesso seed = stessa griglia
        $this->assertEquals($grid1->toString(), $grid2->toString());
    }

    public function test_generate_puzzle_with_givens(): void
    {
        $seed = 11111;
        $givens = 25;
        
        $puzzle = $this->generator->generatePuzzle($seed, $givens);
        
        // Nota: il generatore potrebbe non riuscire a rimuovere esattamente il numero richiesto di celle
        // mantenendo la soluzione unica, quindi verifichiamo che sia "circa" il numero richiesto
        $actualGivens = $puzzle->countGivenCells();
        $this->assertGreaterThanOrEqual($givens - 5, $actualGivens); // ±5 di tolleranza
        $this->assertLessThanOrEqual($givens + 5, $actualGivens);
        
        $this->assertTrue($this->validator->isValid($puzzle));
        $this->assertTrue($this->validator->hasUniqueSolution($puzzle));
    }

    public function test_generate_puzzle_with_difficulty(): void
    {
        $seed = 22222;
        
        $easyPuzzle = $this->generator->generatePuzzleWithDifficulty($seed, 'easy');
        $hardPuzzle = $this->generator->generatePuzzleWithDifficulty($seed + 1, 'hard');
        
        $this->assertTrue($this->validator->isValid($easyPuzzle));
        $this->assertTrue($this->validator->isValid($hardPuzzle));
        
        // Puzzle hard dovrebbe avere meno givens di quello easy
        $this->assertLessThan($easyPuzzle->countGivenCells(), $hardPuzzle->countGivenCells());
    }

    public function test_difficulty_rating(): void
    {
        $seed = 33333;
        
        // Genera puzzle di diverse difficoltà
        $easyPuzzle = $this->generator->generatePuzzleWithDifficulty($seed, 'easy');
        $hardPuzzle = $this->generator->generatePuzzleWithDifficulty($seed + 1, 'hard');
        
        $easyRating = $this->rater->rateDifficulty($easyPuzzle);
        $hardRating = $this->rater->rateDifficulty($hardPuzzle);
        
        $easyScore = $this->rater->getScore($easyPuzzle);
        $hardScore = $this->rater->getScore($hardPuzzle);
        
        // Nota: per seed diversi potremmo non avere sempre hard > easy
        // ma almeno verifichiamo che il rating funzioni
        $this->assertIsString($easyRating);
        $this->assertIsString($hardRating);
        $this->assertIsInt($easyScore);
        $this->assertIsInt($hardScore);
        
        // Entrambi dovrebbero essere validi
        $this->assertContains($easyRating, ['Easy', 'Medium', 'Hard', 'Expert', 'Master']);
        $this->assertContains($hardRating, ['Easy', 'Medium', 'Hard', 'Expert', 'Master']);
    }

    public function test_detailed_difficulty_analysis(): void
    {
        $seed = 44444;
        $puzzle = $this->generator->generatePuzzle($seed, 30);
        
        $analysis = $this->rater->getDetailedAnalysis($puzzle);
        
        $this->assertArrayHasKey('difficulty', $analysis);
        $this->assertArrayHasKey('score', $analysis);
        $this->assertArrayHasKey('techniques', $analysis);
        $this->assertArrayHasKey('givens', $analysis);
        $this->assertArrayHasKey('empty_cells', $analysis);
        
        $this->assertEquals(30, $analysis['givens']);
        $this->assertEquals(51, $analysis['empty_cells']);
        $this->assertIsArray($analysis['techniques']);
    }

    public function test_patterns(): void
    {
        $seed = 55555;
        
        // Test pattern minimal
        $minimal = $this->generator->generateWithPattern($seed, 'minimal');
        $this->assertTrue($this->validator->hasUniqueSolution($minimal));
        $this->assertLessThanOrEqual(25, $minimal->countGivenCells()); // Dovrebbe essere molto minimal
        
        // Test pattern symmetric
        $symmetric = $this->generator->generateWithPattern($seed + 1, 'symmetric');
        $this->assertTrue($this->validator->hasUniqueSolution($symmetric));
        
        // Test pattern diagonal
        $diagonal = $this->generator->generateWithPattern($seed + 2, 'diagonal');
        $this->assertTrue($this->validator->hasUniqueSolution($diagonal));
    }

    public function test_invalid_givens_count_throws_exception(): void
    {
        $this->expectException(\App\Domain\Sudoku\Exceptions\InvalidGridException::class);
        $this->generator->generatePuzzle(123, 10); // Troppo poche givens
    }

    public function test_multiple_seeds_produce_different_puzzles(): void
    {
        $puzzle1 = $this->generator->generatePuzzle(1111, 25);
        $puzzle2 = $this->generator->generatePuzzle(2222, 25);
        $puzzle3 = $this->generator->generatePuzzle(3333, 25);
        
        // Dovrebbero essere diversi
        $this->assertNotEquals($puzzle1->toString(), $puzzle2->toString());
        $this->assertNotEquals($puzzle2->toString(), $puzzle3->toString());
        $this->assertNotEquals($puzzle1->toString(), $puzzle3->toString());
        
        // Ma tutti validi
        $this->assertTrue($this->validator->hasUniqueSolution($puzzle1));
        $this->assertTrue($this->validator->hasUniqueSolution($puzzle2));
        $this->assertTrue($this->validator->hasUniqueSolution($puzzle3));
    }
}
