<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Sudoku;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function test_valid_empty_grid(): void
    {
        $grid = Grid::empty();
        
        $this->assertTrue($this->validator->isValid($grid));
        $this->assertEmpty($this->validator->getValidationErrors($grid));
    }

    public function test_valid_partial_grid(): void
    {
        $str = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($str);
        
        $this->assertTrue($this->validator->isValid($grid));
        $this->assertEmpty($this->validator->getValidationErrors($grid));
    }

    public function test_valid_complete_grid(): void
    {
        $str = "534678912672195348198342567859761423426853791713924856961537284287419635345286179";
        $grid = Grid::fromString($str);
        
        $this->assertTrue($this->validator->isValid($grid));
        $this->assertTrue($this->validator->isSolved($grid));
        $this->assertEmpty($this->validator->getValidationErrors($grid));
    }

    public function test_invalid_grid_with_row_conflict(): void
    {
        // Due 5 nella prima riga
        $values = [
            [5, 5, null, null, 7, null, null, null, null],
            [6, null, null, 1, 9, 5, null, null, null],
            [null, 9, 8, null, null, null, null, 6, null],
            [8, null, null, null, 6, null, null, null, 3],
            [4, null, null, 8, null, 3, null, null, 1],
            [7, null, null, null, 2, null, null, null, 6],
            [null, 6, null, null, null, null, 2, 8, null],
            [null, null, null, 4, 1, 9, null, null, 5],
            [null, null, null, null, 8, null, null, 7, 9]
        ];
        
        $grid = Grid::fromArray($values);
        
        $this->assertFalse($this->validator->isValid($grid));
        
        $errors = $this->validator->getValidationErrors($grid);
        $this->assertArrayHasKey('row_0', $errors);
    }

    public function test_can_place_value(): void
    {
        $str = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($str);
        
        // Può piazzare 4 in posizione (0,2) 
        $this->assertTrue($this->validator->canPlaceValue($grid, 0, 2, 4));
        
        // Non può piazzare 5 in posizione (0,2) perché c'è già un 5 nella riga
        $this->assertFalse($this->validator->canPlaceValue($grid, 0, 2, 5));
        
        // Non può piazzare 6 in posizione (0,2) perché c'è già un 6 nella colonna
        $this->assertFalse($this->validator->canPlaceValue($grid, 0, 2, 6));
    }

    public function test_solvability(): void
    {
        // Puzzle valido solvibile
        $validPuzzle = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($validPuzzle);
        
        $this->assertTrue($this->validator->isSolvable($grid));
        
        // Puzzle già risolto
        $solved = "534678912672195348198342567859761423426853791713924856961537284287419635345286179";
        $solvedGrid = Grid::fromString($solved);
        
        $this->assertTrue($this->validator->isSolvable($solvedGrid));
        $this->assertTrue($this->validator->isSolved($solvedGrid));
    }

    public function test_count_solutions(): void
    {
        // Puzzle con soluzione unica
        $uniquePuzzle = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($uniquePuzzle);
        
        $solutionCount = $this->validator->countSolutions($grid, 2);
        $this->assertEquals(1, $solutionCount);
        $this->assertTrue($this->validator->hasUniqueSolution($grid));
    }

    public function test_find_first_solution(): void
    {
        $puzzle = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($puzzle);
        
        $solution = $this->validator->findFirstSolution($grid);
        
        $this->assertNotNull($solution);
        $this->assertTrue($solution->isComplete());
        $this->assertTrue($this->validator->isValid($solution));
    }

    public function test_impossible_puzzle(): void
    {
        // Puzzle impossibile (due 5 nella stessa riga)
        $impossible = "55..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($impossible);
        
        $this->assertFalse($this->validator->isSolvable($grid));
        $this->assertEquals(0, $this->validator->countSolutions($grid, 1));
        $this->assertNull($this->validator->findFirstSolution($grid));
    }
}
