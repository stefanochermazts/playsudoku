<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTranslation;
use App\Services\OpenAITranslationService;
use App\Jobs\TranslateArticleJob;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Admin Controller for Article Management
 * 
 * Handles CRUD operations for articles, translations,
 * and integration with OpenAI translation service.
 */
class ArticleController extends Controller
{


    /**
     * Display a listing of articles.
     */
    public function index(Request $request): View
    {
        $query = Article::with(['category', 'author', 'translations'])
            ->withCount('translations');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('slug', 'ILIKE', "%{$searchTerm}%")
                  ->orWhereHas('translations', function ($subQ) use ($searchTerm) {
                      $subQ->where('title', 'ILIKE', "%{$searchTerm}%")
                           ->orWhere('content', 'ILIKE', "%{$searchTerm}%");
                  });
            });
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $articles = $query->paginate(20)->withQueryString();
        $categories = ArticleCategory::active()->ordered()->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): View
    {
        $categories = ArticleCategory::active()->ordered()->get();
        return view('admin.articles.create', compact('categories'));
    }

    /**
     * Store a newly created article.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:article_categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:1000',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'featured' => 'boolean',
            'auto_translate' => 'boolean',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Handle featured image upload
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $request->file('featured_image')
                ->store('articles/images', 'public');
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($validated['content']));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        // Create article
        $article = Article::create([
            'category_id' => $validated['category_id'],
            'slug' => $slug,
            'status' => $validated['status'],
            'featured_image' => $featuredImagePath,
            'tags' => $validated['tags'] ?? [],
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'reading_time_minutes' => $readingTime,
            'featured' => $validated['featured'] ?? false,
            'created_by' => Auth::id(),
            'auto_translate' => $validated['auto_translate'] ?? true,
        ]);

        // Create Italian translation (source language)
        ArticleTranslation::create([
            'article_id' => $article->id,
            'locale' => 'it',
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'translation_status' => 'approved',
            'translated_by' => Auth::id(),
            'translated_at' => now(),
            'word_count' => $wordCount,
        ]);

        // Trigger automatic translation if enabled and article is published
        if ($validated['auto_translate'] && $validated['status'] === 'published') {
            TranslateArticleJob::dispatch($article);
        }

        return redirect()
            ->route('admin.articles.show', $article)
            ->with('success', 'Articolo creato con successo!');
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): View
    {
        $article->load(['category', 'author', 'translations']);
        $translationCompleteness = $article->getTranslationCompleteness();
        
        return view('admin.articles.show', compact('article', 'translationCompleteness'));
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load(['translations']);
        $categories = ArticleCategory::active()->ordered()->get();
        $italianTranslation = $article->italianTranslation();
        
        return view('admin.articles.edit', compact('article', 'categories', 'italianTranslation'));
    }

    /**
     * Trigger translation for a specific article.
     */
    public function translate(Article $article): RedirectResponse
    {
        if (!$article->italianTranslation()) {
            return back()->with('error', 'Nessuna traduzione italiana trovata.');
        }

        TranslateArticleJob::dispatch($article);

        return back()->with('success', 'Traduzione avviata in background.');
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $validated = $request->validated();
        
        // Update article
        $article->update([
            'category_id' => $validated['category_id'],
            'slug' => $validated['slug'],
            'status' => $validated['status'],
            'featured_image' => $validated['featured_image'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'published_at' => $validated['status'] === 'published' ? ($validated['published_at'] ?? now()) : null,
            'reading_time_minutes' => $validated['reading_time_minutes'] ?? null,
            'featured' => $validated['featured'] ?? false,
            'auto_translate' => $validated['auto_translate'] ?? false,
            'updated_by' => Auth::id(),
        ]);

        // Update Italian translation
        $italianTranslation = $article->italianTranslation();
        if ($italianTranslation) {
            $wordCount = str_word_count(strip_tags($validated['content']));
            
            $italianTranslation->update([
                'title' => $validated['title'],
                'excerpt' => $validated['excerpt'],
                'content' => $validated['content'],
                'word_count' => $wordCount,
            ]);
        }

        // Trigger automatic translation if enabled and article is published
        if ($validated['auto_translate'] && $validated['status'] === 'published') {
            TranslateArticleJob::dispatch($article);
        }

        return redirect()
            ->route('admin.articles.show', $article)
            ->with('success', 'Articolo aggiornato con successo!');
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article): RedirectResponse
    {
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Articolo eliminato con successo!');
    }
}