<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Sudoku;

use App\Domain\Sudoku\ValueObjects\CandidateSet;
use App\Domain\Sudoku\Exceptions\InvalidCellValueException;
use PHPUnit\Framework\TestCase;

class CandidateSetTest extends TestCase
{
    public function test_empty_set_creation(): void
    {
        $set = CandidateSet::empty();
        
        $this->assertTrue($set->isEmpty());
        $this->assertEquals(0, $set->count());
        $this->assertFalse($set->contains(1));
    }

    public function test_all_candidates_creation(): void
    {
        $set = CandidateSet::all();
        
        $this->assertFalse($set->isEmpty());
        $this->assertEquals(9, $set->count());
        
        for ($i = 1; $i <= 9; $i++) {
            $this->assertTrue($set->contains($i));
        }
    }

    public function test_from_array_creation(): void
    {
        $set = CandidateSet::from([1, 3, 5]);
        
        $this->assertEquals(3, $set->count());
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(3));
        $this->assertTrue($set->contains(5));
        $this->assertFalse($set->contains(2));
        $this->assertFalse($set->contains(4));
    }

    public function test_from_string_creation(): void
    {
        $set = CandidateSet::fromString("135");
        
        $this->assertEquals(3, $set->count());
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(3));
        $this->assertTrue($set->contains(5));
        $this->assertEquals("135", $set->toString());
    }

    public function test_single_candidate(): void
    {
        $set = CandidateSet::single(7);
        
        $this->assertTrue($set->isSingle());
        $this->assertEquals(1, $set->count());
        $this->assertEquals(7, $set->getSingle());
        $this->assertEquals("7", $set->toString());
    }

    public function test_add_remove_operations(): void
    {
        $set = CandidateSet::empty()
            ->add(1)
            ->add(5)
            ->add(9);
        
        $this->assertEquals(3, $set->count());
        $this->assertEquals("159", $set->toString());
        
        $set = $set->remove(5);
        $this->assertEquals(2, $set->count());
        $this->assertEquals("19", $set->toString());
    }

    public function test_set_operations(): void
    {
        $set1 = CandidateSet::from([1, 2, 3]);
        $set2 = CandidateSet::from([2, 3, 4]);
        
        // Intersection
        $intersection = $set1->intersect($set2);
        $this->assertEquals("23", $intersection->toString());
        
        // Union
        $union = $set1->union($set2);
        $this->assertEquals("1234", $union->toString());
        
        // Difference
        $difference = $set1->difference($set2);
        $this->assertEquals("1", $difference->toString());
    }

    public function test_invalid_candidate_throws_exception(): void
    {
        $this->expectException(InvalidCellValueException::class);
        CandidateSet::from([0, 10]);
    }

    public function test_equality(): void
    {
        $set1 = CandidateSet::from([1, 3, 5]);
        $set2 = CandidateSet::from([5, 1, 3]); // Different order
        
        $this->assertTrue($set1->equals($set2));
    }

    public function test_subset_operations(): void
    {
        $subset = CandidateSet::from([1, 3]);
        $superset = CandidateSet::from([1, 2, 3, 4]);
        
        $this->assertTrue($subset->isSubsetOf($superset));
        $this->assertTrue($superset->isSupersetOf($subset));
        $this->assertFalse($superset->isSubsetOf($subset));
    }
}
