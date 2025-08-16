<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\BadgeService;
use App\Models\{User, ActivityFeed};

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        // Seed badge catalogue
        app(BadgeService::class)->ensureSeeded();

        // Optional: create some friends activities demo
        $users = User::take(3)->get();
        if ($users->count() >= 2) {
            $u1 = $users[0];
            $u2 = $users[1];

            // Challenge completed example
            ActivityFeed::createChallengeCompleted($u1, [
                'challenge_id' => 1,
                'difficulty' => 'normal',
                'duration_ms' => 74210,
            ]);

            // Personal record example
            ActivityFeed::createPersonalRecord($u2, [
                'difficulty' => 'easy',
                'new_best_time' => 59320,
            ]);

            // Streak milestone example
            ActivityFeed::createStreakMilestone($u1, 3);
        }
    }
}

