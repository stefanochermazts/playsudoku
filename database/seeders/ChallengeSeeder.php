<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Sudoku\Generator;
use App\Domain\Sudoku\Validator;
use App\Models\Challenge;
use App\Models\Puzzle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $validator = new Validator();
        $generator = new Generator($validator);
        
        // Trova l'admin user, o crea uno fittizio per il seeder
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'role' => 'admin',
            ]);
        }

        // Crea puzzle per le sfide
        $puzzles = [];
        $difficulties = ['easy', 'normal', 'hard', 'expert', 'crazy'];
        
        foreach ($difficulties as $difficulty) {
            // Crea 3 puzzle per ogni difficoltà
            for ($i = 0; $i < 3; $i++) {
                $seed = abs(crc32("test_{$difficulty}_{$i}_" . date('Y_m_d'))) % 1000000;
                
                // Controlla se il puzzle esiste già
                $existingPuzzle = Puzzle::where('seed', $seed)->where('difficulty', $difficulty)->first();
                if ($existingPuzzle) {
                    $puzzles[$difficulty][] = $existingPuzzle;
                    continue;
                }
                
                $puzzle_grid = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
                $solution_grid = $generator->generateCompleteGrid($seed);
                
                $puzzle = Puzzle::create([
                    'seed' => $seed,
                    'givens' => $puzzle_grid->toArray(),
                    'solution' => $solution_grid->toArray(),
                    'difficulty' => $difficulty,
                ]);
                
                $puzzles[$difficulty][] = $puzzle;
            }
        }

        // Crea sfide daily (oggi, ieri, domani)
        $dates = [
            Carbon::now()->subDay(),  // Ieri (completabile)
            Carbon::now(),            // Oggi (attiva)
            Carbon::now()->addDay(),  // Domani (programmata)
        ];
        
        foreach ($dates as $index => $date) {
            $difficulty = $difficulties[$index % count($difficulties)];
            $puzzle = $puzzles[$difficulty][0];
            
            Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'daily',
                'title' => "Daily Challenge - " . $date->format('Y-m-d'),
                'description' => "Daily challenge for " . $date->format('F j, Y'),
                'starts_at' => $date->startOfDay(),
                'ends_at' => $date->endOfDay(),
                'status' => $date->isToday() || $date->isPast() ? 'active' : 'scheduled',
                'visibility' => 'public',
                'created_by' => $admin->id,
                'settings' => [
                    'hints_allowed' => false,
                    'time_limit' => null,
                    'max_attempts' => 1,
                ],
            ]);
        }

        // Crea sfide weekly (questa settimana, scorsa settimana, prossima settimana)
        $weeklyDates = [
            Carbon::now()->subWeek()->startOfWeek(),  // Scorsa settimana
            Carbon::now()->startOfWeek(),             // Questa settimana
            Carbon::now()->addWeek()->startOfWeek(),  // Prossima settimana
        ];
        
        foreach ($weeklyDates as $index => $date) {
            $difficulty = $difficulties[($index + 2) % count($difficulties)];
            $puzzle = $puzzles[$difficulty][1];
            
            Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'weekly',
                'title' => "Weekly Challenge - Week " . $date->format('W'),
                'description' => "Weekly challenge for week " . $date->format('W') . " of " . $date->format('Y'),
                'starts_at' => $date,
                'ends_at' => $date->copy()->endOfWeek(),
                'status' => $date->isPast() || $date->isCurrentWeek() ? 'active' : 'scheduled',
                'visibility' => 'public',
                'created_by' => $admin->id,
                'settings' => [
                    'hints_allowed' => true,
                    'time_limit' => null,
                    'max_attempts' => 3,
                ],
            ]);
        }

        // Crea alcune sfide custom
        $customTitles = [
            'Speed Challenge' => 'Test your speed with this quick puzzle!',
            'Logic Master' => 'For true Sudoku masters only.',
            'Weekend Special' => 'A special weekend challenge.',
        ];
        
        $customIndex = 0;
        foreach ($customTitles as $title => $description) {
            $difficulty = $difficulties[$customIndex % count($difficulties)];
            $puzzle = $puzzles[$difficulty][2];
            
            $startsAt = Carbon::now()->addHours($customIndex * 6);
            
            Challenge::create([
                'puzzle_id' => $puzzle->id,
                'type' => 'custom',
                'title' => $title,
                'description' => $description,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addDays(3),
                'status' => 'active',
                'visibility' => 'public',
                'created_by' => $admin->id,
                'settings' => [
                    'hints_allowed' => $customIndex > 0,
                    'time_limit' => $customIndex === 0 ? 1800000 : null, // 30 minuti per Speed Challenge
                    'max_attempts' => $customIndex + 1,
                ],
            ]);
            
            $customIndex++;
        }

        $this->command->info('Created ' . Challenge::count() . ' challenges with ' . Puzzle::count() . ' puzzles');
    }
}