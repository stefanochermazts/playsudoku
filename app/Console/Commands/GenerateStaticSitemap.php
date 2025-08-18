<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class GenerateStaticSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--force : Force regeneration even if files exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate static sitemap.xml and robots.txt files for SEO';

    private array $locales = ['en', 'it', 'de', 'es'];
    private string $sitemapPath;
    private string $robotsPath;

    public function __construct()
    {
        parent::__construct();
        $this->sitemapPath = public_path('sitemap.xml');
        $this->robotsPath = public_path('robots.txt');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🗺️  Generating static sitemap and robots.txt...');

        try {
            // Check if files exist and --force not used
            if (!$this->option('force') && (File::exists($this->sitemapPath) || File::exists($this->robotsPath))) {
                if (!$this->confirm('Sitemap or robots.txt already exist. Overwrite?')) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
            }

            // Generate sitemap.xml
            $this->generateSitemap();
            
            // Generate robots.txt
            $this->generateRobots();

            $this->info('✅ Static sitemap and robots.txt generated successfully!');
            $this->info("📄 Sitemap: {$this->sitemapPath}");
            $this->info("🤖 Robots: {$this->robotsPath}");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating sitemap: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Generate static sitemap.xml file
     */
    private function generateSitemap(): void
    {
        $this->info('📝 Generating sitemap.xml...');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Public static pages only
        $publicPages = [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'localized.sudoku.training', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'localized.sudoku.analyzer', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['route' => 'localized.daily-board.index', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'localized.weekly-board.index', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['route' => 'localized.help', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'localized.contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['route' => 'localized.privacy', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['route' => 'localized.cookie-policy', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['route' => 'localized.terms', 'priority' => '0.4', 'changefreq' => 'yearly'],
        ];

        $urlCount = 0;

        foreach ($publicPages as $page) {
            foreach ($this->locales as $locale) {
                try {
                    if ($page['route'] === 'home') {
                        $url = $locale === 'it' ? url('/') : url('/' . $locale);
                    } else {
                        $url = route($page['route'], ['locale' => $locale]);
                    }

                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . now()->toISOString() . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
                    $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";

                    // Add alternate language versions (hreflang)
                    foreach ($this->locales as $altLocale) {
                        if ($page['route'] === 'home') {
                            $altUrl = $altLocale === 'it' ? url('/') : url('/' . $altLocale);
                        } else {
                            $altUrl = route($page['route'], ['locale' => $altLocale]);
                        }
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . $altLocale . '" href="' . htmlspecialchars($altUrl) . '" />' . "\n";
                    }

                    $xml .= '  </url>' . "\n";
                    $urlCount++;

                } catch (\Exception $e) {
                    $this->warn("⚠️  Skipped invalid route: {$page['route']} for locale {$locale}");
                    continue;
                }
            }
        }

        // Add public challenges (non-authenticated access only)
        $publicChallenges = Challenge::where('status', 'active')
            ->where('visibility', 'public')
            ->orderBy('created_at', 'desc')
            ->take(100) // Limit to avoid huge sitemaps
            ->get();

        foreach ($publicChallenges as $challenge) {
            foreach ($this->locales as $locale) {
                try {
                    // Only include challenge listing pages, not play pages (require auth)
                    if ($challenge->type === 'daily') {
                        $url = route('localized.daily-board.show', [
                            'locale' => $locale,
                            'date' => $challenge->starts_at->format('Y-m-d')
                        ]);
                    } elseif ($challenge->type === 'weekly') {
                        $url = route('localized.weekly-board.show', [
                            'locale' => $locale,
                            'week' => $challenge->starts_at->format('Y-\WW')
                        ]);
                    } else {
                        continue; // Skip other challenge types
                    }

                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . $challenge->updated_at->toISOString() . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                    $xml .= '    <priority>0.6</priority>' . "\n";

                    // Add alternate language versions
                    foreach ($this->locales as $altLocale) {
                        if ($challenge->type === 'daily') {
                            $altUrl = route('localized.daily-board.show', [
                                'locale' => $altLocale,
                                'date' => $challenge->starts_at->format('Y-m-d')
                            ]);
                        } else {
                            $altUrl = route('localized.weekly-board.show', [
                                'locale' => $altLocale,
                                'week' => $challenge->starts_at->format('Y-\WW')
                            ]);
                        }
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . $altLocale . '" href="' . htmlspecialchars($altUrl) . '" />' . "\n";
                    }

                    $xml .= '  </url>' . "\n";
                    $urlCount++;

                } catch (\Exception $e) {
                    $this->warn("⚠️  Skipped invalid challenge: {$challenge->id}");
                    continue;
                }
            }
        }

        $xml .= '</urlset>';

        // Save sitemap file
        File::put($this->sitemapPath, $xml);
        $this->info("✅ Sitemap generated with {$urlCount} URLs");
    }

    /**
     * Generate static robots.txt file
     */
    private function generateRobots(): void
    {
        $this->info('🤖 Generating robots.txt...');

        $content = "# PlaySudoku Robots.txt - Generated on " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $content .= "User-agent: *\n";
        $content .= "Allow: /\n\n";
        
        // Disallow admin and private areas
        $content .= "# Admin and private areas\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /nova/\n";
        $content .= "Disallow: /horizon/\n\n";
        
        // Disallow auth and user-specific pages
        $content .= "# Authentication and user areas\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password/\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /settings\n";
        $content .= "Disallow: /impersonation/\n\n";
        
        // Disallow challenge play pages (require authentication)
        $content .= "# Challenge play (requires authentication)\n";
        $content .= "Disallow: /challenges/*/play\n";
        $content .= "Disallow: */daily-board/*/play\n";
        $content .= "Disallow: */weekly-board/*/play\n\n";
        
        // Disallow API endpoints
        $content .= "# API endpoints\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /webhooks/\n\n";
        
        // Allow assets and public resources
        $content .= "# Public resources\n";
        $content .= "Allow: /build/\n";
        $content .= "Allow: /storage/\n";
        $content .= "Allow: /images/\n";
        $content .= "Allow: /css/\n";
        $content .= "Allow: /js/\n\n";
        
        // Crawl delay for different bots
        $content .= "# Crawl delays\n";
        $content .= "User-agent: Googlebot\n";
        $content .= "Crawl-delay: 1\n\n";
        
        $content .= "User-agent: Bingbot\n";
        $content .= "Crawl-delay: 2\n\n";
        
        // Sitemap location
        $content .= "# Sitemap\n";
        $content .= "Sitemap: " . url('sitemap.xml') . "\n";

        // Save robots.txt file
        File::put($this->robotsPath, $content);
        $this->info('✅ Robots.txt generated');
    }
}
