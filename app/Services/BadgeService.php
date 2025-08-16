<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\{Badge, User, UserBadge};

class BadgeService
{
    public function ensureSeeded(): void
    {
        $seed = [
            ['slug' => 'first_win', 'name' => 'Prima Vittoria', 'category' => 'achievement', 'points' => 50, 'icon' => 'medal', 'color' => 'primary-500', 'description' => 'Completa la tua prima sfida.'],
            ['slug' => 'five_wins', 'name' => 'Cinque Vittorie', 'category' => 'consistency', 'points' => 100, 'icon' => 'trophy', 'color' => 'secondary-500', 'description' => 'Completa 5 sfide.'],
            ['slug' => 'speedster_60s', 'name' => 'Speedster', 'category' => 'performance', 'points' => 100, 'icon' => 'bolt', 'color' => 'amber-500', 'description' => 'Completa una sfida in meno di 60 secondi.'],
            ['slug' => 'hard_solver', 'name' => 'Hard Solver', 'category' => 'achievement', 'points' => 120, 'icon' => 'shield', 'color' => 'violet-500', 'description' => 'Completa una sfida Hard o superiore.'],
            ['slug' => 'no_hints', 'name' => 'No Hints', 'category' => 'performance', 'points' => 80, 'icon' => 'lightbulb', 'color' => 'emerald-500', 'description' => 'Completa senza usare hint.'],
            ['slug' => 'perfect_run', 'name' => 'Perfect Run', 'category' => 'performance', 'points' => 120, 'icon' => 'target', 'color' => 'rose-500', 'description' => 'Completa senza errori.'],
            ['slug' => 'weekly_warrior', 'name' => 'Weekly Warrior', 'category' => 'consistency', 'points' => 90, 'icon' => 'calendar', 'color' => 'cyan-500', 'description' => 'Completa una sfida settimanale.'],
            ['slug' => 'social_starter', 'name' => 'Social Starter', 'category' => 'social', 'points' => 40, 'icon' => 'users', 'color' => 'pink-500', 'description' => 'Aggiungi un amico.'],
            ['slug' => 'club_member', 'name' => 'Club Member', 'category' => 'social', 'points' => 60, 'icon' => 'flag', 'color' => 'teal-500', 'description' => 'Unisciti a un club.'],
        ];
        foreach ($seed as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], $b);
        }
    }

    public function onChallengeCompleted(User $user, array $challenge): void
    {
        $this->ensureSeeded();
        $this->awardOnce($user, 'first_win');

        $wins = $user->completedAttempts()->count();
        if ($wins >= 5) {
            $this->awardOnce($user, 'five_wins');
        }

        if (($challenge['duration_ms'] ?? 0) > 0 && $challenge['duration_ms'] <= 60000) {
            $this->awardOnce($user, 'speedster_60s');
        }

        $difficulty = strtolower((string)($challenge['difficulty'] ?? ''));
        if (in_array($difficulty, ['hard', 'expert', 'crazy'], true)) {
            $this->awardOnce($user, 'hard_solver');
        }

        if ((int)($challenge['hints_used'] ?? 0) === 0) {
            $this->awardOnce($user, 'no_hints');
        }
        if ((int)($challenge['errors_count'] ?? 0) === 0) {
            $this->awardOnce($user, 'perfect_run');
        }

        if (($challenge['type'] ?? null) === 'weekly') {
            $this->awardOnce($user, 'weekly_warrior');
        }
    }

    public function onFriendAdded(User $user): void
    {
        $this->ensureSeeded();
        $this->awardOnce($user, 'social_starter');
    }

    public function onClubJoined(User $user): void
    {
        $this->ensureSeeded();
        $this->awardOnce($user, 'club_member');
    }

    private function awardOnce(User $user, string $badgeSlug): void
    {
        $badge = Badge::where('slug', $badgeSlug)->first();
        if (!$badge) { return; }
        $existing = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->first();
        if ($existing && $existing->completed) { return; }
        UserBadge::updateOrCreate(
            ['user_id' => $user->id, 'badge_id' => $badge->id],
            ['completed' => true, 'awarded_at' => now(), 'progress' => 1]
        );
    }
}
