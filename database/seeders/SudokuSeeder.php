<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Puzzle;
use App\Models\Challenge;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SudokuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea alcuni utenti di test
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@playsudoku.com',
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => 'Test Player 1',
            'email' => 'player1@example.com',
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Test Player 2',
            'email' => 'player2@example.com',
            'role' => 'user',
        ]);

        // Crea profili per gli utenti
        UserProfile::factory()->create([
            'user_id' => $admin->id,
            'country' => 'IT',
            'preferences_json' => [
                'theme' => 'dark',
                'language' => 'it',
                'notifications' => [
                    'email_challenges' => true,
                    'email_results' => true,
                ]
            ]
        ]);

        UserProfile::factory()->create([
            'user_id' => $user1->id,
            'country' => 'US',
            'preferences_json' => [
                'theme' => 'light',
                'language' => 'en',
            ]
        ]);

        UserProfile::factory()->create([
            'user_id' => $user2->id,
            'country' => 'FR',
            'preferences_json' => [
                'theme' => 'light',
                'language' => 'en',
            ]
        ]);

        // Crea alcuni puzzle di test
        $easyPuzzle = Puzzle::factory()->easy()->create();
        $hardPuzzle = Puzzle::factory()->hard()->create();

        // Crea sfide di test
        Challenge::factory()->create([
            'puzzle_id' => $easyPuzzle->id,
            'type' => 'daily',
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->endOfDay(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        Challenge::factory()->create([
            'puzzle_id' => $hardPuzzle->id,
            'type' => 'weekly',
            'starts_at' => now()->startOfWeek(),
            'ends_at' => now()->endOfWeek(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->command->info('Sudoku test data seeded successfully!');
    }
}
