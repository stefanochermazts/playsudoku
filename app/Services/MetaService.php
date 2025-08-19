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
    private array $hreflang = [];
    private bool $isConfigured = false;

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

        // SEO optimized titles (max 60 characters)
        $seoTitle = match($locale) {
            'it' => 'Sudoku Online Gratis | Gioca Sudoku Facili e Difficili',
            'de' => 'Sudoku Online Kostenlos | Leichte & Schwere Rätsel',
            'es' => 'Sudoku Online Gratis | Juega Sudokus Fáciles y Difíciles',
            default => 'Play Sudoku Online Free | Easy & Hard Sudoku Puzzles',
        };

        $this->meta = [
            'title' => $seoTitle,
            'description' => match($locale) {
                'it' => 'Gioca a Sudoku online gratis! Sfide quotidiane, classifiche competitive, modalità allenamento e analizzatore puzzle. La piattaforma Sudoku più completa per principianti ed esperti.',
                'de' => 'Spielen Sie Sudoku kostenlos online! Tägliche Herausforderungen, Bestenlisten, Trainingsmodus und Puzzle-Analysator. Die vollständigste Sudoku-Plattform für Anfänger und Experten.',
                'es' => '¡Juega Sudoku gratis en línea! Desafíos diarios, clasificaciones competitivas, modo entrenamiento y analizador de puzzles. La plataforma de Sudoku más completa para principiantes y expertos.',
                default => 'Play Sudoku online for free! Daily challenges, competitive leaderboards, training mode and puzzle analyzer. The most complete Sudoku platform for beginners and experts.',
            },
            'keywords' => match($locale) {
                'it' => 'sudoku, puzzle, gioco, online, gratis, sfide, classifica, allenamento, brain training',
                'de' => 'sudoku, puzzle, spiel, online, kostenlos, herausforderungen, bestenliste, training, gehirntraining',
                'es' => 'sudoku, puzzle, juego, en línea, gratis, desafíos, clasificación, entrenamiento, ejercicio mental',
                default => 'sudoku, puzzle, game, online, free, challenges, leaderboard, training, brain training',
            },
            'author' => 'PlaySudoku',
            'robots' => 'index, follow',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'charset' => 'UTF-8',
        ];

        $this->openGraph = [
            'og:title' => $seoTitle,
            'og:description' => $this->meta['description'],
            'og:type' => 'website',
            'og:url' => url()->current(),
            'og:site_name' => $appName,
            'og:locale' => match($locale) {
                'it' => 'it_IT',
                'de' => 'de_DE', 
                'es' => 'es_ES',
                default => 'en_US',
            },
            'og:image' => url('img/playsudoku_club.png'),
            'og:image:type' => 'image/png',
            'og:image:width' => '1200',
            'og:image:height' => '630',
            'og:image:alt' => match($locale) {
                'it' => 'PlaySudoku - Gioca a Sudoku Online',
                'de' => 'PlaySudoku - Spielen Sie Sudoku Online',
                'es' => 'PlaySudoku - Juega Sudoku en Línea',
                default => 'PlaySudoku - Play Sudoku Online',
            },
        ];

        $this->twitterCard = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $seoTitle,
            'twitter:description' => $this->meta['description'],
            'twitter:site' => '@PlaySudoku',
            'twitter:image' => url('img/playsudoku_club.png'),
            'twitter:image:alt' => match($locale) {
                'it' => 'PlaySudoku - Gioca a Sudoku Online',
                'de' => 'PlaySudoku - Spielen Sie Sudoku Online',
                'es' => 'PlaySudoku - Juega Sudoku en Línea',
                default => 'PlaySudoku - Play Sudoku Online',
            },
        ];

        // Initialize hreflang tags
        $this->setHreflang();

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
        
        // SEO optimized titles for Training
        $title = match($locale) {
            'it' => 'Allenamento Sudoku Gratis | Migliora le Tue Abilità',
            'de' => 'Kostenlos Sudoku Trainieren | Verbessern Sie Ihre Fähigkeiten',
            'es' => 'Entrenamiento Sudoku Gratis | Mejora Tus Habilidades',
            default => 'Free Sudoku Training | Improve Your Skills',
        };
        
        $description = match($locale) {
            'it' => 'Allenati gratuitamente con il nostro Sudoku interattivo. Difficoltà variabili, suggerimenti intelligenti e analisi delle tecniche di risoluzione.',
            'de' => 'Trainieren Sie kostenlos mit unserem interaktiven Sudoku. Variable Schwierigkeiten, intelligente Tipps und Analyse der Lösungstechniken.',
            'es' => 'Entrénate gratis con nuestro Sudoku interactivo. Dificultades variables, pistas inteligentes y análisis de técnicas de resolución.',
            default => 'Train for free with our interactive Sudoku. Variable difficulties, smart hints and solving technique analysis.',
        };

        $this->setPage($title, $description);
        $this->isConfigured = true;
        
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
        
        // SEO optimized titles for Analyzer
        $title = match($locale) {
            'it' => 'Analizzatore Sudoku | Risolvi Ogni Puzzle Facilmente',
            'de' => 'Sudoku Analysator | Lösen Sie Jedes Rätsel Einfach',
            'es' => 'Analizador Sudoku | Resuelve Cualquier Puzzle Fácilmente',
            default => 'Sudoku Analyzer | Solve Any Puzzle Easily',
        };
        
        $description = match($locale) {
            'it' => 'Analizza qualsiasi puzzle Sudoku con il nostro risolutore avanzato. Scopri tutte le tecniche necessarie per la risoluzione passo dopo passo.',
            'de' => 'Analysieren Sie jedes Sudoku-Rätsel mit unserem fortschrittlichen Löser. Entdecken Sie alle Techniken für die schrittweise Lösung.',
            'es' => 'Analiza cualquier puzzle Sudoku con nuestro solucionador avanzado. Descubre todas las técnicas necesarias para la resolución paso a paso.',
            default => 'Analyze any Sudoku puzzle with our advanced solver. Discover all the techniques needed for step-by-step resolution.',
        };

        $this->setPage($title, $description);
        $this->isConfigured = true;
        
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
        
        // SEO optimized titles for Challenge (max 60 chars)
        $title = match($locale) {
            'it' => "Sfida Sudoku {$difficulty} | Competizione Online",
            'de' => "Sudoku Herausforderung {$difficulty} | Online Wettbewerb",
            'es' => "Desafío Sudoku {$difficulty} | Competición Online",
            default => "Sudoku Challenge {$difficulty} | Online Competition",
        };
        
        $description = match($locale) {
            'it' => "Partecipa alla sfida Sudoku {$type} di difficoltà {$difficulty}. Competizione in tempo reale con classifica globale.",
            'de' => "Nehmen Sie an der {$type} Sudoku-Herausforderung der Schwierigkeit {$difficulty} teil. Echtzeit-Wettbewerb mit globaler Bestenliste.",
            'es' => "Únete al desafío Sudoku {$type} de dificultad {$difficulty}. Competición en tiempo real con clasificación global.",
            default => "Join the {$type} Sudoku challenge of {$difficulty} difficulty. Real-time competition with global leaderboard.",
        };

        $this->setPage($title, $description);
        $this->isConfigured = true;
        
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
        
        // SEO optimized titles for Leaderboard (max 60 chars)
        $title = match($locale) {
            'it' => "Classifica Sudoku {$difficulty} | Migliori Tempi",
            'de' => "Sudoku Bestenliste {$difficulty} | Beste Zeiten",
            'es' => "Clasificación Sudoku {$difficulty} | Mejores Tiempos",
            default => "Sudoku Leaderboard {$difficulty} | Best Times",
        };
        
        $description = match($locale) {
            'it' => "Visualizza la classifica della sfida Sudoku {$type} di difficoltà {$difficulty}. Scopri i migliori tempi e le prestazioni dei giocatori.",
            'de' => "Sehen Sie sich die Bestenliste der {$type} Sudoku-Herausforderung der Schwierigkeit {$difficulty} an. Entdecken Sie die besten Zeiten und Spielerleistungen.",
            'es' => "Ve la clasificación del desafío Sudoku {$type} de dificultad {$difficulty}. Descubre los mejores tiempos y rendimientos de los jugadores.",
            default => "View the leaderboard for the {$type} Sudoku challenge of {$difficulty} difficulty. Discover the best times and player performances.",
        };

        $this->setPage($title, $description);
        $this->isConfigured = true;
        
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
            'schemaOrg' => $this->schemaOrg,
            'hreflang' => $this->hreflang
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

    /**
     * Set hreflang tags for all supported locales.
     */
    private function setHreflang(): void
    {
        $supportedLocales = config('app.supported_locales', ['en', 'it', 'de', 'es']);
        $currentUrl = url()->current();
        $currentLocale = app()->getLocale();
        
        // Remove locale from current URL to get base URL
        $baseUrl = $this->getBaseUrlWithoutLocale($currentUrl, $currentLocale);
        
        // Add hreflang for each supported locale
        foreach ($supportedLocales as $locale) {
            $this->hreflang[$locale] = $baseUrl . '/' . $locale;
        }
        
        // Add x-default pointing to English
        $this->hreflang['x-default'] = $baseUrl . '/en';
        
        // Set canonical to current locale URL
        $this->meta['canonical'] = $baseUrl . '/' . $currentLocale;
    }

    /**
     * Get base URL without locale prefix.
     */
    private function getBaseUrlWithoutLocale(string $url, string $currentLocale): string
    {
        $baseUrl = config('app.url');
        $path = str_replace($baseUrl, '', $url);
        
        // Remove leading slash and locale if present
        $path = ltrim($path, '/');
        if (str_starts_with($path, $currentLocale . '/') || $path === $currentLocale) {
            $path = substr($path, strlen($currentLocale));
            $path = ltrim($path, '/');
        }
        
        return $baseUrl . ($path ? '/' . $path : '');
    }

    /**
     * Get hreflang tags.
     */
    public function getHreflang(): array
    {
        return $this->hreflang;
    }

    /**
     * Update hreflang for specific URL path.
     */
    public function setCustomHreflang(string $pathWithoutLocale): void
    {
        $supportedLocales = config('app.supported_locales', ['en', 'it', 'de', 'es']);
        $baseUrl = config('app.url');
        
        foreach ($supportedLocales as $locale) {
            $this->hreflang[$locale] = $baseUrl . '/' . $locale . ($pathWithoutLocale ? '/' . $pathWithoutLocale : '');
        }
        
        $this->hreflang['x-default'] = $baseUrl . '/en' . ($pathWithoutLocale ? '/' . $pathWithoutLocale : '');
    }

    /**
     * Check if MetaService has been configured with specific page data.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }
}