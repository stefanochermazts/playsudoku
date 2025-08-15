<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea utente admin se non esiste
        User::firstOrCreate(
            ['email' => 'admin@playsudoku.club'],
            [
                'name' => 'Admin PlaySudoku',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Crea alcuni utenti di test
        User::factory(10)->create();
    }
}
