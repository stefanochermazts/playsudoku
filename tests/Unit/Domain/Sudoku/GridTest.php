<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Sudoku;

use App\Domain\Sudoku\Grid;
use App\Domain\Sudoku\Exceptions\InvalidGridException;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    public function test_empty_grid_creation(): void
    {
        $grid = Grid::empty();
        
        $this->assertEquals(81, $grid->countEmptyCells());
        $this->assertEquals(0, $grid->countGivenCells());
        $this->assertFalse($grid->isComplete());
        $this->assertTrue($grid->isValid()); // Empty grid is valid
    }

    public function test_from_array_creation(): void
    {
        $values = [
            [5, 3, null, null, 7, null, null, null, null],
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
        
        $this->assertEquals(5, $grid->getCell(0, 0)->value);
        $this->assertTrue($grid->getCell(0, 0)->isGiven);
        $this->assertNull($grid->getCell(0, 2)->value);
        $this->assertTrue($grid->getCell(0, 2)->isEmpty());
        $this->assertTrue($grid->isValid());
    }

    public function test_from_string_creation(): void
    {
        $str = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        
        $grid = Grid::fromString($str);
        
        $this->assertEquals(5, $grid->getCell(0, 0)->value);
        $this->assertEquals(3, $grid->getCell(0, 1)->value);
        $this->assertNull($grid->getCell(0, 2)->value);
        $this->assertTrue($grid->isValid());
    }

    public function test_set_and_clear_cell(): void
    {
        $grid = Grid::empty();
        
        // Set a value
        $grid = $grid->setCell(0, 0, 5);
        $this->assertEquals(5, $grid->getCell(0, 0)->value);
        $this->assertFalse($grid->getCell(0, 0)->isGiven);
        
        // Clear the value
        $grid = $grid->clearCell(0, 0);
        $this->assertNull($grid->getCell(0, 0)->value);
        $this->assertTrue($grid->getCell(0, 0)->isEmpty());
    }

    public function test_get_row_col_box(): void
    {
        $str = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($str);
        
        // Test row
        $row0 = $grid->getRow(0);
        $this->assertEquals(9, count($row0));
        $this->assertEquals(5, $row0[0]->value);
        $this->assertEquals(3, $row0[1]->value);
        
        // Test column
        $col0 = $grid->getCol(0);
        $this->assertEquals(9, count($col0));
        $this->assertEquals(5, $col0[0]->value);
        $this->assertEquals(6, $col0[1]->value);
        
        // Test box
        $box0 = $grid->getBox(0); // Top-left box
        $this->assertEquals(9, count($box0));
        $this->assertEquals(5, $box0[0]->value); // (0,0)
        $this->assertEquals(3, $box0[1]->value); // (0,1)
    }

    public function test_peers(): void
    {
        $grid = Grid::empty();
        $peers = $grid->getPeers(4, 4); // Center cell
        
        $this->assertArrayHasKey('row', $peers);
        $this->assertArrayHasKey('col', $peers);
        $this->assertArrayHasKey('box', $peers);
        
        $this->assertEquals(8, count($peers['row'])); // 8 other cells in row
        $this->assertEquals(8, count($peers['col'])); // 8 other cells in col
        $this->assertEquals(8, count($peers['box'])); // 8 other cells in box
    }

    public function test_valid_sudoku(): void
    {
        // Valid completed sudoku
        $validStr = "534678912672195348198342567859761423426853791713924856961537284287419635345286179";
        $grid = Grid::fromString($validStr);
        
        $this->assertTrue($grid->isValid());
        $this->assertTrue($grid->isComplete());
        $this->assertTrue($grid->isSolved());
    }

    public function test_invalid_sudoku(): void
    {
        // Invalid sudoku (duplicate 5 in first row)
        $invalidValues = [
            [5, 5, null, null, 7, null, null, null, null], // Two 5s in row
            [6, null, null, 1, 9, 5, null, null, null],
            [null, 9, 8, null, null, null, null, 6, null],
            [8, null, null, null, 6, null, null, null, 3],
            [4, null, null, 8, null, 3, null, null, 1],
            [7, null, null, null, 2, null, null, null, 6],
            [null, 6, null, null, null, null, 2, 8, null],
            [null, null, null, 4, 1, 9, null, null, 5],
            [null, null, null, null, 8, null, null, 7, 9]
        ];
        
        $grid = Grid::fromArray($invalidValues);
        
        $this->assertFalse($grid->isValid());
    }

    public function test_to_string_conversion(): void
    {
        $originalStr = "53..7....6..195....98....6.8...6...34..8.3..17...2...6.6....28....419..5....8..79";
        $grid = Grid::fromString($originalStr);
        $convertedStr = $grid->toString();
        
        $this->assertEquals($originalStr, $convertedStr);
    }

    public function test_to_array_conversion(): void
    {
        $originalValues = [
            [5, 3, null, null, 7, null, null, null, null],
            [6, null, null, 1, 9, 5, null, null, null],
            [null, 9, 8, null, null, null, null, 6, null],
            [8, null, null, null, 6, null, null, null, 3],
            [4, null, null, 8, null, 3, null, null, 1],
            [7, null, null, null, 2, null, null, null, 6],
            [null, 6, null, null, null, null, 2, 8, null],
            [null, null, null, 4, 1, 9, null, null, 5],
            [null, null, null, null, 8, null, null, 7, 9]
        ];
        
        $grid = Grid::fromArray($originalValues);
        $convertedArray = $grid->toArray();
        
        $this->assertEquals($originalValues, $convertedArray);
    }

    public function test_invalid_string_length_throws_exception(): void
    {
        $this->expectException(InvalidGridException::class);
        Grid::fromString("123"); // Too short
    }

    public function test_invalid_grid_dimensions_throw_exception(): void
    {
        $this->expectException(InvalidGridException::class);
        Grid::fromArray([
            [1, 2, 3], // Wrong size
            [4, 5, 6]
        ]);
    }
}
