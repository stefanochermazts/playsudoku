<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Challenge;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

class BreadcrumbService
{
    private array $breadcrumbs = [];

    /**
     * Generate breadcrumbs for the current route.
     */
    public function generate(): array
    {
        $this->breadcrumbs = [];
        $routeName = Route::currentRouteName();
        
        // Handle case when no current route (e.g., CLI/Tinker)
        $currentRoute = Route::current();
        if (!$currentRoute) {
            return $this->breadcrumbs;
        }
        
        $parameters = $currentRoute->parameters();

        // Always start with home
        $this->addHome();

        // Generate breadcrumbs based on current route
        match (true) {
            // Daily Board
            str_contains($routeName, 'daily-board.archive') => $this->addDailyBoardArchive(),
            str_contains($routeName, 'daily-board.show') => $this->addDailyBoardShow($parameters),
            str_contains($routeName, 'daily-board.index') => $this->addDailyBoardIndex(),
            // Weekly Board
            str_contains($routeName, 'weekly-board.archive') => $this->addWeeklyBoardArchive(),
            str_contains($routeName, 'weekly-board.show') => $this->addWeeklyBoardShow($parameters),
            str_contains($routeName, 'weekly-board.index') => $this->addWeeklyBoardIndex(),
            // Other sections
            str_contains($routeName, 'dashboard') => $this->addDashboard(),
            str_contains($routeName, 'profile') => $this->addProfile(),
            str_contains($routeName, 'sudoku.training') => $this->addTraining(),
            str_contains($routeName, 'sudoku.play') => $this->addTrainingPlay(),
            str_contains($routeName, 'sudoku.analyzer') => $this->addAnalyzer(),
            str_contains($routeName, 'challenges.play') => $this->addChallengePlay($parameters),
            str_contains($routeName, 'leaderboard.show') => $this->addLeaderboard($parameters),
            str_contains($routeName, 'activity') => $this->addActivity(),
            str_contains($routeName, 'help') => $this->addHelp(),
            str_contains($routeName, 'contact') => $this->addContact(),
            str_contains($routeName, 'privacy') => $this->addPrivacy(),
            str_contains($routeName, 'cookie-policy') => $this->addCookiePolicy(),
            str_contains($routeName, 'terms') => $this->addTerms(),
            default => null
        };

        return $this->breadcrumbs;
    }

    /**
     * Get breadcrumbs as JSON-LD structured data.
     */
    public function getStructuredData(): array
    {
        $breadcrumbs = $this->generate();
        
        if (empty($breadcrumbs)) {
            return [];
        }

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        foreach ($breadcrumbs as $index => $breadcrumb) {
            $structuredData['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['title'],
                'item' => $breadcrumb['url']
            ];
        }

        return $structuredData;
    }

    private function addHome(): void
    {
        $locale = app()->getLocale();
        $url = $locale === 'it' ? url('/') : url('/' . $locale);
        
        $this->breadcrumbs[] = [
            'title' => __('app.nav.home'),
            'url' => $url,
            'current' => false
        ];
    }

    private function addDashboard(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.dashboard'),
            'url' => route('localized.dashboard', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addProfile(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.profile'),
            'url' => route('localized.profile', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addTraining(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.training'),
            'url' => route('localized.sudoku.training', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addTrainingPlay(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.training'),
            'url' => route('localized.sudoku.training', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        
        $this->breadcrumbs[] = [
            'title' => __('app.training.play_now'),
            'url' => route('localized.sudoku.play', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addAnalyzer(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.analyzer'),
            'url' => route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addDailyBoardIndex(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.daily_board'),
            'url' => route('localized.daily-board.index', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addDailyBoardArchive(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.daily_board'),
            'url' => route('localized.daily-board.index', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        $this->breadcrumbs[] = [
            'title' => __('app.daily_board_archive'),
            'url' => route('localized.daily-board.archive', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addDailyBoardShow(array $parameters): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.daily_board'),
            'url' => route('localized.daily-board.index', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        $this->breadcrumbs[] = [
            'title' => __('app.daily_board_archive'),
            'url' => route('localized.daily-board.archive', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        if (isset($parameters['date'])) {
            $this->breadcrumbs[] = [
                'title' => (string) $parameters['date'],
                'url' => route('localized.daily-board.show', ['locale' => app()->getLocale(), 'date' => $parameters['date']]),
                'current' => true
            ];
        }
    }

    private function addWeeklyBoardIndex(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.weekly_board'),
            'url' => route('localized.weekly-board.index', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addWeeklyBoardArchive(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.weekly_board'),
            'url' => route('localized.weekly-board.index', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        $this->breadcrumbs[] = [
            'title' => __('app.weekly_board_archive'),
            'url' => route('localized.weekly-board.archive', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addWeeklyBoardShow(array $parameters): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.weekly_board'),
            'url' => route('localized.weekly-board.index', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        $this->breadcrumbs[] = [
            'title' => __('app.weekly_board_archive'),
            'url' => route('localized.weekly-board.archive', ['locale' => app()->getLocale()]),
            'current' => false
        ];
        if (isset($parameters['week'])) {
            $this->breadcrumbs[] = [
                'title' => (string) $parameters['week'],
                'url' => route('localized.weekly-board.show', ['locale' => app()->getLocale(), 'week' => $parameters['week']]),
                'current' => true
            ];
        }
    }

    private function addChallengePlay(array $parameters): void
    {
        if (isset($parameters['challenge'])) {
            $challengeId = $parameters['challenge'];
            
            try {
                $challenge = Challenge::findOrFail($challengeId);
                
                $this->breadcrumbs[] = [
                    'title' => __('app.nav.challenges'),
                    'url' => route('localized.dashboard', ['locale' => app()->getLocale()]) . '#challenges',
                    'current' => false
                ];
                
                $this->breadcrumbs[] = [
                    'title' => __('app.challenges.play_challenge', ['type' => ucfirst($challenge->type), 'difficulty' => ucfirst($challenge->puzzle->difficulty ?? 'medium')]),
                    'url' => route('localized.challenges.play', ['locale' => app()->getLocale(), 'challenge' => $challengeId]),
                    'current' => true
                ];
            } catch (\Exception $e) {
                $this->breadcrumbs[] = [
                    'title' => __('app.nav.challenges'),
                    'url' => '#',
                    'current' => true
                ];
            }
        }
    }

    private function addLeaderboard(array $parameters): void
    {
        if (isset($parameters['challenge'])) {
            $challengeId = $parameters['challenge'];
            
            try {
                $challenge = Challenge::findOrFail($challengeId);
                
                $this->breadcrumbs[] = [
                    'title' => __('app.nav.challenges'),
                    'url' => route('localized.dashboard', ['locale' => app()->getLocale()]) . '#challenges',
                    'current' => false
                ];
                
                $this->breadcrumbs[] = [
                    'title' => __('app.leaderboard.title', ['id' => $challengeId]),
                    'url' => route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challengeId]),
                    'current' => true
                ];
            } catch (\Exception $e) {
                $this->breadcrumbs[] = [
                    'title' => __('app.nav.leaderboards'),
                    'url' => '#',
                    'current' => true
                ];
            }
        }
    }

    private function addActivity(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.activity.title'),
            'url' => route('localized.activity.index', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addHelp(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.help'),
            'url' => route('localized.help', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addContact(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.nav.contact'),
            'url' => route('localized.contact', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addPrivacy(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.privacy.title'),
            'url' => route('localized.privacy', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addCookiePolicy(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.cookies.title'),
            'url' => route('localized.cookie-policy', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }

    private function addTerms(): void
    {
        $this->breadcrumbs[] = [
            'title' => __('app.terms.title'),
            'url' => route('localized.terms', ['locale' => app()->getLocale()]),
            'current' => true
        ];
    }
}
