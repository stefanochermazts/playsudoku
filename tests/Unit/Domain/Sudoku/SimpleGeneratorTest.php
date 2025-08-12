<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Sudoku;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use App\Domain\Sudoku\Grid;
use PHPUnit\Framework\TestCase;

class SimpleGeneratorTest extends TestCase
{
    private Generator $generator;
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
        $this->generator = new Generator($this->validator);
    }

    public function test_simple_manual_fill(): void
    {
        // Test per vedere se riusciamo a riempire manualmente una griglia
        $grid = Grid::empty();
        
        // Prova a mettere valori semplici
        $grid = $grid->setCell(0, 0, 1);
        $grid = $grid->setCell(0, 1, 2);
        $grid = $grid->setCell(0, 2, 3);
        
        $this->assertEquals(1, $grid->getCell(0, 0)->value);
        $this->assertEquals(2, $grid->getCell(0, 1)->value);
        $this->assertEquals(3, $grid->getCell(0, 2)->value);
        
        $this->assertTrue($this->validator->isValid($grid));
    }

    public function test_can_place_value_basic(): void
    {
        $grid = Grid::empty();
        
        // Dovrebbe poter piazzare qualsiasi valore in una griglia vuota
        $this->assertTrue($this->validator->canPlaceValue($grid, 0, 0, 1));
        $this->assertTrue($this->validator->canPlaceValue($grid, 0, 0, 5));
        $this->assertTrue($this->validator->canPlaceValue($grid, 0, 0, 9));
        
        // Metti un valore e verifica i vincoli
        $grid = $grid->setCell(0, 0, 5);
        
        // Non dovrebbe poter mettere un altro 5 nella stessa riga
        $this->assertFalse($this->validator->canPlaceValue($grid, 0, 1, 5));
        
        // Non dovrebbe poter mettere un altro 5 nella stessa colonna
        $this->assertFalse($this->validator->canPlaceValue($grid, 1, 0, 5));
        
        // Non dovrebbe poter mettere un altro 5 nello stesso box
        $this->assertFalse($this->validator->canPlaceValue($grid, 1, 1, 5));
        
        // Ma dovrebbe poter mettere 5 in un altro box
        $this->assertTrue($this->validator->canPlaceValue($grid, 3, 3, 5));
    }

    public function test_simple_box_fill(): void
    {
        $grid = Grid::empty();
        
        // Riempi il primo box (0,0 - 2,2) con valori 1-9
        $values = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $index = 0;
        
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 3; $col++) {
                $grid = $grid->setCell($row, $col, $values[$index]);
                $index++;
            }
        }
        
        $this->assertTrue($this->validator->isValid($grid));
        
        // Verifica che non possiamo mettere duplicati
        $this->assertFalse($this->validator->canPlaceValue($grid, 0, 3, 1)); // 1 già nella riga 0
        $this->assertFalse($this->validator->canPlaceValue($grid, 3, 0, 1)); // 1 già nella colonna 0
    }

    public function test_debug_generator_step_by_step(): void
    {
        // Test debugging del generator
        $this->generator->setSeed(123);
        
        $grid = Grid::empty();
        
        // Verifica che possiamo mettere almeno alcuni valori
        $this->assertTrue($this->validator->canPlaceValue($grid, 0, 0, 1));
        
        $grid = $grid->setCell(0, 0, 1);
        $this->assertEquals(1, $grid->getCell(0, 0)->value);
        
        // Prova il passo successivo
        $validValues = [];
        for ($value = 1; $value <= 9; $value++) {
            if ($this->validator->canPlaceValue($grid, 0, 1, $value)) {
                $validValues[] = $value;
            }
        }
        
        // Dovremmo avere 8 valori validi (tutti tranne 1)
        $this->assertEquals(8, count($validValues));
        $this->assertNotContains(1, $validValues);
    }
}
