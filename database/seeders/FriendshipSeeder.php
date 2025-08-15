<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Database\Seeder;

class FriendshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ottieni alcuni utenti esistenti
        $users = User::limit(10)->get();
        
        if ($users->count() < 3) {
            // Se non ci sono abbastanza utenti, creane alcuni
            $users = User::factory()->count(10)->create();
        }

        // Crea alcune amicizie tra gli utenti esistenti
        $admin = $users->first(); // Assume che il primo sia admin
        
        // Amicizie accettate per l'admin
        for ($i = 1; $i <= 3; $i++) {
            if (isset($users[$i])) {
                Friendship::factory()
                    ->accepted()
                    ->create([
                        'user_id' => $admin->id,
                        'friend_id' => $users[$i]->id,
                    ]);
            }
        }

        // Richieste in attesa per l'admin
        for ($i = 4; $i <= 6; $i++) {
            if (isset($users[$i])) {
                Friendship::factory()
                    ->pending()
                    ->withMessage('Hi! Let\'s be friends and challenge each other at Sudoku!')
                    ->create([
                        'user_id' => $users[$i]->id,
                        'friend_id' => $admin->id,
                    ]);
            }
        }

        // Alcune amicizie casuali tra altri utenti
        for ($i = 0; $i < 15; $i++) {
            $user1 = $users->random();
            $user2 = $users->random();
            
            // Evita auto-amicizie e duplicati
            if ($user1->id !== $user2->id) {
                $exists = Friendship::where(function ($query) use ($user1, $user2) {
                    $query->where('user_id', $user1->id)
                          ->where('friend_id', $user2->id);
                })->orWhere(function ($query) use ($user1, $user2) {
                    $query->where('user_id', $user2->id)
                          ->where('friend_id', $user1->id);
                })->exists();

                if (!$exists) {
                    Friendship::factory()->create([
                        'user_id' => $user1->id,
                        'friend_id' => $user2->id,
                    ]);
                }
            }
        }

        $this->command->info('Friendship data seeded successfully!');
        $this->command->info('- Admin user has 3 friends and 3 pending requests');
        $this->command->info('- Created additional random friendships between users');
    }
}
