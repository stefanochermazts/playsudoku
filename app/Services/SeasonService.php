<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{Season, SeasonLeaderboard, User, Challenge};

class SeasonService
{
    public function current(): ?Season
    {
        $now = now();
        return Season::where('starts_at','<=',$now)->where('ends_at','>=',$now)->where('is_active',true)->first();
    }

    public function awardPointsForChallenge(User $user, Challenge $challenge, int $placement = 1): void
    {
        $season = $this->current();
        if (!$season) return;

        // Simple scoring: base + placement bonus
        $base = $challenge->type === 'weekly' ? 20 : 10;
        $bonus = max(0, 10 - ($placement - 1)); // 10 for 1st, 9 for 2nd ...
        $points = $base + $bonus;

        $row = SeasonLeaderboard::firstOrCreate([
            'season_id' => $season->id,
            'user_id' => $user->id,
        ]);

        $row->points += $points;
        $row->participations += 1;
        if ($placement === 1) $row->wins += 1;
        $row->save();
    }
}

