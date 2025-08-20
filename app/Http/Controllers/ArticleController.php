<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Frontend Controller for Article Display
 * 
 * Handles public viewing of articles and categories.
 */
class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request): View
    {
        $query = Article::published()
            ->with(['category', 'author'])
            ->withTranslation();

        // Filter by category
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Featured first, then by publication date
        $articles = $query->orderBy('featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Get active categories for sidebar
        $categories = Cache::remember('article_categories_active', 3600, function () {
            return ArticleCategory::active()
                ->ordered()
                ->withArticleCounts()
                ->get();
        });

        // Get featured articles for sidebar
        $featuredArticles = Cache::remember('featured_articles_sidebar', 1800, function () {
            return Article::published()
                ->featured()
                ->withTranslation()
                ->with('category')
                ->orderBy('published_at', 'desc')
                ->take(5)
                ->get();
        });

        return view('articles.index', compact('articles', 'categories', 'featuredArticles'));
    }

    /**
     * Display articles from a specific category.
     */
    public function category(Request $request, string $categorySlug): View
    {
        $category = ArticleCategory::where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        $query = Article::published()
            ->inCategory($categorySlug)
            ->with(['author'])
            ->withTranslation();

        // Search within category
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $articles = $query->orderBy('featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Get other categories for navigation
        $categories = Cache::remember('article_categories_active', 3600, function () {
            return ArticleCategory::active()
                ->ordered()
                ->withArticleCounts()
                ->get();
        });

        return view('articles.category', compact('articles', 'category', 'categories'));
    }

    /**
     * Display the specified article.
     */
    public function show(string $categorySlug, string $articleSlug): View
    {
        // Find the category
        $category = ArticleCategory::where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        // Find the article
        $article = Article::where('slug', $articleSlug)
            ->where('category_id', $category->id)
            ->published()
            ->with(['category', 'author', 'translations'])
            ->withTranslation()
            ->firstOrFail();

        // Check if article has translation for current locale
        $translation = $article->translation();
        if (!$translation) {
            // Fallback to Italian if no translation available
            $translation = $article->italianTranslation();
            if (!$translation) {
                abort(404, 'Article content not available');
            }
        }

        // Increment view count
        $article->incrementViews();

        // Get related articles (same category, exclude current)
        $relatedArticles = Cache::remember(
            "related_articles_{$article->id}_" . app()->getLocale(),
            1800,
            function () use ($article, $category) {
                return Article::published()
                    ->where('category_id', $category->id)
                    ->where('id', '!=', $article->id)
                    ->withTranslation()
                    ->with('category')
                    ->orderBy('published_at', 'desc')
                    ->take(4)
                    ->get();
            }
        );

        // Get article navigation (previous/next in category)
        $previousArticle = Article::published()
            ->where('category_id', $category->id)
            ->where('published_at', '<', $article->published_at)
            ->withTranslation()
            ->orderBy('published_at', 'desc')
            ->first();

        $nextArticle = Article::published()
            ->where('category_id', $category->id)
            ->where('published_at', '>', $article->published_at)
            ->withTranslation()
            ->orderBy('published_at', 'asc')
            ->first();

        return view('articles.show', compact(
            'article',
            'category',
            'translation',
            'relatedArticles',
            'previousArticle',
            'nextArticle'
        ));
    }

    /**
     * Search articles across all categories.
     */
    public function search(Request $request): View
    {
        $searchTerm = $request->get('q', '');
        $articles = collect();
        $categories = [];

        if (strlen($searchTerm) >= 3) {
            $articles = Article::published()
                ->search($searchTerm)
                ->with(['category', 'author'])
                ->withTranslation()
                ->orderBy('published_at', 'desc')
                ->paginate(12)
                ->withQueryString();

            // Get categories for filtering
            $categories = Cache::remember('article_categories_active', 3600, function () {
                return ArticleCategory::active()
                    ->ordered()
                    ->withArticleCounts()
                    ->get();
            });
        }

        return view('articles.search', compact('articles', 'categories', 'searchTerm'));
    }

    /**
     * Display articles feed (for homepage or widgets).
     */
    public function feed(Request $request): View
    {
        $limit = (int) $request->get('limit', 6);
        $categorySlug = $request->get('category');

        $query = Article::published()
            ->with(['category'])
            ->withTranslation();

        if ($categorySlug) {
            $query->inCategory($categorySlug);
        }

        $articles = $query->orderBy('featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();

        return view('articles.feed', compact('articles'));
    }

    /**
     * Get article sitemap data.
     */
    public function sitemap(): array
    {
        return Cache::remember('articles_sitemap', 3600, function () {
            return Article::published()
                ->with('category')
                ->orderBy('published_at', 'desc')
                ->get()
                ->map(function ($article) {
                    return [
                        'url' => $article->url,
                        'lastmod' => $article->updated_at->format('Y-m-d'),
                        'changefreq' => 'monthly',
                        'priority' => $article->featured ? '0.8' : '0.6',
                    ];
                })
                ->toArray();
        });
    }
}