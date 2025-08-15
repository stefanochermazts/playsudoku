<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class PerformanceService
{
    /**
     * Get critical CSS for a specific page type.
     */
    public function getCriticalCss(string $pageType = 'default'): string
    {
        $cacheKey = "critical_css_{$pageType}";
        
        return Cache::remember($cacheKey, 3600, function () use ($pageType) {
            $criticalCssPath = resource_path("css/critical/{$pageType}.css");
            
            if (File::exists($criticalCssPath)) {
                return File::get($criticalCssPath);
            }
            
            // Fallback to default critical CSS
            $defaultPath = resource_path('css/critical/default.css');
            if (File::exists($defaultPath)) {
                return File::get($defaultPath);
            }
            
            // Generate minimal critical CSS if none exists
            return $this->generateMinimalCriticalCss();
        });
    }

    /**
     * Generate minimal critical CSS for above-the-fold content.
     */
    private function generateMinimalCriticalCss(): string
    {
        return <<<CSS
/* Critical CSS - Above the fold */
body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif}
.header{position:relative;background:#fff;border-bottom:1px solid #e5e7eb}
.nav{display:flex;align-items:center;justify-content:space-between;padding:1rem}
.main{min-height:50vh}
.breadcrumb{background:#f9fafb;border-bottom:1px solid #e5e7eb;padding:.75rem 0}
@media (prefers-color-scheme:dark){
.header{background:#111827;border-color:#374151}
.breadcrumb{background:#1f2937;border-color:#374151}
}
CSS;
    }

    /**
     * Get resource hints for current page.
     */
    public function getResourceHints(): array
    {
        return [
            'dns-prefetch' => [
                '//fonts.googleapis.com',
                '//fonts.gstatic.com',
                '//www.google-analytics.com',
                '//www.googletagmanager.com',
            ],
            'preconnect' => [
                'https://fonts.gstatic.com',
            ],
            'prefetch' => [
                // Dinamically added based on page
            ],
            'preload' => [
                // Critical resources - will be added dynamically
            ]
        ];
    }

    /**
     * Get preload directives for critical resources.
     */
    public function getCriticalResourcePreloads(): array
    {
        $preloads = [];
        
        // Preload critical fonts if using web fonts
        $preloads[] = [
            'href' => 'https://fonts.gstatic.com/s/inter/v12/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hiA.woff2',
            'as' => 'font',
            'type' => 'font/woff2',
            'crossorigin' => 'anonymous'
        ];
        
        return $preloads;
    }

    /**
     * Get page-specific performance optimizations.
     */
    public function getPageOptimizations(string $routeName): array
    {
        $optimizations = [
            'lazy_loading' => true,
            'critical_css' => 'default',
            'defer_non_critical' => true,
            'prefetch_navigation' => true,
        ];

        // Page-specific optimizations
        switch (true) {
            case str_contains($routeName, 'challenges.play'):
                $optimizations['critical_css'] = 'game';
                $optimizations['preload_game_assets'] = true;
                break;
                
            case str_contains($routeName, 'sudoku.training'):
                $optimizations['critical_css'] = 'training';
                $optimizations['preload_puzzle_engine'] = true;
                break;
                
            case str_contains($routeName, 'leaderboard'):
                $optimizations['critical_css'] = 'leaderboard';
                $optimizations['defer_charts'] = true;
                break;
                
            case str_contains($routeName, 'home'):
                $optimizations['critical_css'] = 'homepage';
                $optimizations['prefetch_dashboard'] = true;
                break;
        }

        return $optimizations;
    }

    /**
     * Generate WebP variants for images (if not exists).
     */
    public function ensureWebPVariants(string $imagePath): array
    {
        $variants = ['original' => $imagePath];
        
        if (!str_ends_with($imagePath, '.svg')) {
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $imagePath);
            $avifPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.avif', $imagePath);
            
            // In produzione, questi file dovrebbero essere generati durante il build
            if (File::exists(public_path($webpPath))) {
                $variants['webp'] = $webpPath;
            }
            
            if (File::exists(public_path($avifPath))) {
                $variants['avif'] = $avifPath;
            }
        }
        
        return $variants;
    }

    /**
     * Get optimized srcset for responsive images.
     */
    public function generateResponsiveSrcset(string $imagePath, array $sizes = [320, 640, 1024, 1280]): string
    {
        $srcset = [];
        $baseUrl = asset($imagePath);
        $pathInfo = pathinfo($imagePath);
        
        foreach ($sizes as $size) {
            // In un ambiente di produzione, queste immagini ridimensionate dovrebbero
            // essere generate durante il processo di build o tramite un servizio di image optimization
            $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $size . 'w.' . $pathInfo['extension'];
            
            if (File::exists(public_path($resizedPath))) {
                $srcset[] = asset($resizedPath) . " {$size}w";
            }
        }
        
        // Fallback alla immagine originale se non ci sono varianti
        if (empty($srcset)) {
            $srcset[] = $baseUrl . " 1x";
        }
        
        return implode(', ', $srcset);
    }

    /**
     * Get performance metrics configuration.
     */
    public function getPerformanceConfig(): array
    {
        return [
            'enable_lazy_loading' => config('app.env') === 'production',
            'enable_critical_css' => config('app.env') === 'production',
            'enable_resource_hints' => true,
            'enable_prefetch' => config('app.env') === 'production',
            'image_optimization' => [
                'webp' => true,
                'avif' => false, // Può essere abilitato quando il supporto browser migliora
                'lazy_loading_threshold' => '50px',
                'blur_placeholder' => false,
            ],
            'core_web_vitals' => [
                'track_cls' => true,
                'track_lcp' => true,
                'track_fid' => true,
                'track_inp' => true,
                'report_to_analytics' => config('analytics.enabled'),
            ]
        ];
    }
}
