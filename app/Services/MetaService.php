<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Support\Str;

class MetaService
{
    private array $meta = [];
    private array $openGraph = [];
    private array $twitterCard = [];
    private array $schemaOrg = [];

    public function __construct()
    {
        $this->setDefaults();
    }

    /**
     * Set default meta tags for the application.
     */
    private function setDefaults(): void
    {
        $locale = app()->getLocale();
        $appName = __('app.app_name');

        $this->meta = [
            'title' => $appName,
            'description' => $locale === 'it' 
                ? 'Gioca a Sudoku online gratis! Sfide quotidiane, classifiche competitive, modalità allenamento e analizzatore puzzle. La piattaforma Sudoku più completa per principianti ed esperti.'
                : 'Play Sudoku online for free! Daily challenges, competitive leaderboards, training mode and puzzle analyzer. The most complete Sudoku platform for beginners and experts.',
            'keywords' => $locale === 'it'
                ? 'sudoku, puzzle, gioco, online, gratis, sfide, classifica, allenamento, brain training'
                : 'sudoku, puzzle, game, online, free, challenges, leaderboard, training, brain training',
            'author' => 'PlaySudoku',
            'robots' => 'index, follow',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'charset' => 'UTF-8',
        ];

        $this->openGraph = [
            'og:title' => $appName,
            'og:description' => $this->meta['description'],
            'og:type' => 'website',
            'og:url' => url()->current(),
            'og:site_name' => $appName,
            'og:locale' => $locale === 'it' ? 'it_IT' : 'en_US',
            'og:image' => url('img/playsudoku_club.png'),
            'og:image:type' => 'image/png',
            'og:image:width' => '1200',
            'og:image:height' => '630',
            'og:image:alt' => $locale === 'it' ? 'PlaySudoku - Gioca a Sudoku Online' : 'PlaySudoku - Play Sudoku Online',
        ];

        $this->twitterCard = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $appName,
            'twitter:description' => $this->meta['description'],
            'twitter:site' => '@PlaySudoku',
            'twitter:image' => url('img/playsudoku_club.png'),
            'twitter:image:alt' => $locale === 'it' ? 'PlaySudoku - Gioca a Sudoku Online' : 'PlaySudoku - Play Sudoku Online',
        ];

        $this->schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $appName,
            'description' => $this->meta['description'],
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    /**
     * Set meta tags for a generic page.
     */
    public function setPage(string $title, string $description = null, array $options = []): void
    {
        $this->meta['title'] = $title;
        
        if ($description) {
            $this->meta['description'] = $description;
        }

        $this->openGraph['og:title'] = $title;
        $this->openGraph['og:description'] = $this->meta['description'];
        
        if (isset($options['url'])) {
            $this->openGraph['og:url'] = $options['url'];
            $this->meta['canonical'] = $options['url'];
        }

        // Preserve default social image unless overridden
        if (isset($options['image'])) {
            $this->openGraph['og:image'] = $options['image'];
            $this->twitterCard['twitter:image'] = $options['image'];
        }

        $this->twitterCard['twitter:title'] = $title;
        $this->twitterCard['twitter:description'] = $this->meta['description'];
    }

    /**
     * Set meta tags for Training page.
     */
    public function setTraining(): void
    {
        $locale = app()->getLocale();
        $title = __('app.training.title');
        $description = $locale === 'it' 
            ? 'Allenati gratuitamente con il nostro Sudoku interattivo. Difficoltà variabili, suggerimenti intelligenti e analisi delle tecniche di risoluzione.'
            : 'Train for free with our interactive Sudoku. Variable difficulties, smart hints and solving technique analysis.';

        $this->setPage($title, $description);
        
        $this->openGraph['og:type'] = 'article';
        $this->schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'Game',
            'name' => $title,
            'description' => $description,
            'gameLocation' => 'Online',
            'numberOfPlayers' => 'Single Player',
            'genre' => 'Puzzle'
        ];
    }

    /**
     * Set meta tags for Analyzer page.
     */
    public function setAnalyzer(): void
    {
        $locale = app()->getLocale();
        $title = __('app.analyzer.title');
        $description = $locale === 'it' 
            ? 'Analizza qualsiasi puzzle Sudoku con il nostro risolutore avanzato. Scopri tutte le tecniche necessarie per la risoluzione passo dopo passo.'
            : 'Analyze any Sudoku puzzle with our advanced solver. Discover all the techniques needed for step-by-step resolution.';

        $this->setPage($title, $description);
        
        $this->openGraph['og:type'] = 'article';
        $this->schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $title,
            'description' => $description,
            'applicationCategory' => 'GameApplication',
            'operatingSystem' => 'Web Browser'
        ];
    }

    /**
     * Set meta tags for Challenge page.
     */
    public function setChallenge(Challenge $challenge): void
    {
        $locale = app()->getLocale();
        $difficulty = ucfirst($challenge->puzzle->difficulty ?? 'medium');
        $type = ucfirst($challenge->type);
        
        $title = $locale === 'it' 
            ? "Sfida Sudoku {$type} - Difficoltà {$difficulty}"
            : "Sudoku Challenge {$type} - {$difficulty} Difficulty";
            
        $description = $locale === 'it'
            ? "Partecipa alla sfida Sudoku {$type} di difficoltà {$difficulty}. Competizione in tempo reale con classifica globale."
            : "Join the {$type} Sudoku challenge of {$difficulty} difficulty. Real-time competition with global leaderboard.";

        $this->setPage($title, $description);
        
        $this->openGraph['og:type'] = 'article';
        $this->schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'Game',
            'name' => $title,
            'description' => $description,
            'gameLocation' => 'Online',
            'numberOfPlayers' => 'Multiple Players',
            'genre' => 'Puzzle',
            'difficulty' => $difficulty
        ];
    }

    /**
     * Set meta tags for Leaderboard page.
     */
    public function setLeaderboard(Challenge $challenge): void
    {
        $locale = app()->getLocale();
        $difficulty = ucfirst($challenge->puzzle->difficulty ?? 'medium');
        $type = ucfirst($challenge->type);
        
        $title = $locale === 'it' 
            ? "Classifica Sfida #{$challenge->id} - {$type} {$difficulty}"
            : "Challenge #{$challenge->id} Leaderboard - {$type} {$difficulty}";
            
        $description = $locale === 'it'
            ? "Visualizza la classifica della sfida Sudoku {$type} di difficoltà {$difficulty}. Scopri i migliori tempi e le prestazioni dei giocatori."
            : "View the leaderboard for the {$type} Sudoku challenge of {$difficulty} difficulty. Discover the best times and player performances.";

        $this->setPage($title, $description);
        
        $this->openGraph['og:type'] = 'article';
        $this->schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $title,
            'description' => $description,
            'numberOfItems' => $challenge->getTotalValidParticipants()
        ];
    }

    /**
     * Get all meta tags as array.
     */
    public function getAllMeta(): array
    {
        return [
            'meta' => $this->meta,
            'openGraph' => $this->openGraph,
            'twitterCard' => $this->twitterCard,
            'schemaOrg' => $this->schemaOrg
        ];
    }

    /**
     * Get specific meta value.
     */
    public function get(string $key): ?string
    {
        return $this->meta[$key] ?? null;
    }

    /**
     * Get Open Graph data.
     */
    public function getOpenGraph(): array
    {
        return $this->openGraph;
    }

    /**
     * Get Twitter Card data.
     */
    public function getTwitterCard(): array
    {
        return $this->twitterCard;
    }

    /**
     * Get Schema.org data.
     */
    public function getSchemaOrg(): array
    {
        return $this->schemaOrg;
    }
}