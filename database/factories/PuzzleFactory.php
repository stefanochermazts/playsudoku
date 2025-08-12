<?php

namespace Database\Factories;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Puzzle>
 */
class PuzzleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seed = $this->faker->numberBetween(1000, 999999);
        $difficulty = $this->faker->randomElement(['easy', 'normal', 'hard', 'expert', 'crazy']);
        
        // Usa il domain generator per creare un puzzle reale
        $validator = new Validator();
        $generator = new Generator($validator);
        
        $puzzle = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
        $solution = $generator->generateCompleteGrid($seed);
        
        return [
            'seed' => $seed,
            'givens' => $puzzle->toArray(),
            'solution' => $solution->toArray(),
            'difficulty' => $difficulty,
        ];
    }

    /**
     * Indicate that the puzzle should be easy.
     */
    public function easy(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'easy',
        ]);
    }

    /**
     * Indicate that the puzzle should be hard.
     */
    public function hard(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'hard',
        ]);
    }
}
