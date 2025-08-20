<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

/**
 * Admin Controller for Article Category Management
 * 
 * Handles CRUD operations for article categories.
 */
class ArticleCategoryController extends Controller
{
    /**
     * Display a listing of article categories.
     */
    public function index(): View
    {
        $categories = ArticleCategory::withCount('articles')
            ->orderBy('sort_order')
            ->orderBy('name_it')
            ->get();

        return view('admin.article-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('admin.article-categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:article_categories,slug',
            'name_it' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_de' => 'required|string|max:255',
            'name_es' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'sort_order' => 'required|integer|min:0|max:100',
            'active' => 'boolean',
        ]);

        ArticleCategory::create($validated);

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Categoria creata con successo!');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(ArticleCategory $articleCategory): View
    {
        $category = $articleCategory->loadCount('articles');
        return view('admin.article-categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:article_categories,slug,' . $articleCategory->id,
            'name_it' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_de' => 'required|string|max:255',
            'name_es' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'sort_order' => 'required|integer|min:0|max:100',
            'active' => 'boolean',
        ]);

        $articleCategory->update($validated);

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Categoria aggiornata con successo!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(ArticleCategory $articleCategory): RedirectResponse
    {
        // Check if category has articles
        if ($articleCategory->articles()->exists()) {
            return back()->with('error', 'Impossibile eliminare una categoria che contiene articoli.');
        }

        $articleCategory->delete();

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Categoria eliminata con successo!');
    }
}