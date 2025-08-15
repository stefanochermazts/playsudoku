<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    private array $locales = ['it', 'en'];

    /**
     * Generate main sitemap index.
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages sitemap
        $xml .= $this->addSitemapEntry(route('sitemap.static'), now()->toISOString());
        
        // Challenges sitemap
        $xml .= $this->addSitemapEntry(route('sitemap.challenges'), now()->toISOString());

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate static pages sitemap.
     */
    public function static(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Static pages
        $staticPages = [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'localized.sudoku.training', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'localized.sudoku.analyzer', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['route' => 'localized.help', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'localized.contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'localized.privacy', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['route' => 'localized.cookie-policy', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['route' => 'localized.terms', 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page) {
            foreach ($this->locales as $locale) {
                try {
                    if ($page['route'] === 'home') {
                        $url = $locale === 'it' ? url('/') : url('/' . $locale);
                    } else {
                        $url = route($page['route'], ['locale' => $locale]);
                    }

                    $xml .= '<url>' . "\n";
                    $xml .= '  <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
                    $xml .= '  <lastmod>' . now()->toISOString() . '</lastmod>' . "\n";
                    $xml .= '  <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
                    $xml .= '  <priority>' . $page['priority'] . '</priority>' . "\n";

                    // Add alternate language versions
                    foreach ($this->locales as $altLocale) {
                        if ($page['route'] === 'home') {
                            $altUrl = $altLocale === 'it' ? url('/') : url('/' . $altLocale);
                        } else {
                            $altUrl = route($page['route'], ['locale' => $altLocale]);
                        }
                        $xml .= '  <xhtml:link rel="alternate" hreflang="' . $altLocale . '" href="' . htmlspecialchars($altUrl) . '" />' . "\n";
                    }

                    $xml .= '</url>' . "\n";
                } catch (\Exception $e) {
                    // Skip invalid routes
                    continue;
                }
            }
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=86400', // 24 hours
        ]);
    }

    /**
     * Generate challenges sitemap.
     */
    public function challenges(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Get recent public challenges (last 6 months)
        $challenges = Challenge::where('created_at', '>=', now()->subMonths(6))
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(1000) // Limit for performance
            ->get();

        foreach ($challenges as $challenge) {
            foreach ($this->locales as $locale) {
                try {
                    // Challenge play page (only for logged users, but include in sitemap for discovery)
                    $playUrl = route('localized.challenges.play', ['locale' => $locale, 'challenge' => $challenge->id]);
                    
                    // Leaderboard page (public)
                    $leaderboardUrl = route('localized.leaderboard.show', ['locale' => $locale, 'challenge' => $challenge->id]);

                    // Add leaderboard URL (public)
                    $xml .= '<url>' . "\n";
                    $xml .= '  <loc>' . htmlspecialchars($leaderboardUrl) . '</loc>' . "\n";
                    $xml .= '  <lastmod>' . $challenge->updated_at->toISOString() . '</lastmod>' . "\n";
                    $xml .= '  <changefreq>weekly</changefreq>' . "\n";
                    $xml .= '  <priority>0.7</priority>' . "\n";

                    // Add alternate language versions for leaderboard
                    foreach ($this->locales as $altLocale) {
                        $altUrl = route('localized.leaderboard.show', ['locale' => $altLocale, 'challenge' => $challenge->id]);
                        $xml .= '  <xhtml:link rel="alternate" hreflang="' . $altLocale . '" href="' . htmlspecialchars($altUrl) . '" />' . "\n";
                    }

                    $xml .= '</url>' . "\n";
                } catch (\Exception $e) {
                    // Skip invalid routes
                    continue;
                }
            }
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600', // 1 hour
        ]);
    }

    /**
     * Generate robots.txt
     */
    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "\n";
        
        // Disallow admin areas
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /nova/\n";
        $content .= "\n";
        
        // Disallow auth/user specific pages
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password/\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /challenges/*/play\n"; // Challenge play requires auth
        $content .= "\n";
        
        // Disallow API endpoints
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        
        // Allow assets
        $content .= "Allow: /build/\n";
        $content .= "Allow: /storage/\n";
        $content .= "Allow: /images/\n";
        $content .= "\n";
        
        // Crawl delay for heavy crawlers
        $content .= "User-agent: Googlebot\n";
        $content .= "Crawl-delay: 1\n";
        $content .= "\n";
        
        // Sitemap location
        $content .= "Sitemap: " . route('sitemap.index') . "\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400', // 24 hours
        ]);
    }

    /**
     * Helper to add sitemap entry to index.
     */
    private function addSitemapEntry(string $url, string $lastmod): string
    {
        $xml = '<sitemap>' . "\n";
        $xml .= '  <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
        $xml .= '  <lastmod>' . $lastmod . '</lastmod>' . "\n";
        $xml .= '</sitemap>' . "\n";
        
        return $xml;
    }
}
