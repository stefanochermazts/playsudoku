<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        Season::updateOrCreate(
            ['slug' => $start->format('Y-m')],
            [
                'name' => 'Stagione ' . $start->translatedFormat('F Y'),
                'starts_at' => $start,
                'ends_at' => $end,
                'is_active' => true,
            ]
        );
    }
}



