<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArticleGeneratorService;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;

/**
 * Article Generator Controller
 * 
 * Handles AI-powered article generation with SEO optimization.
 * Provides a step-by-step wizard interface.
 */
class ArticleGeneratorController extends Controller
{
    public function __construct(
        private ArticleGeneratorService $generatorService
    ) {}

    /**
     * Show the article generator wizard.
     */
    public function index(): View
    {
        if (!$this->generatorService->isConfigured()) {
            return view('admin.articles.generator.not-configured');
        }

        $categories = ArticleCategory::active()->ordered()->get();
        
        return view('admin.articles.generator.wizard', compact('categories'));
    }

    /**
     * Generate article outline based on user input.
     */
    public function generateOutline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'keywords' => 'required|array|min:1|max:10',
            'keywords.*' => 'required|string|max:100',
            'target_audience' => 'required|string|in:principianti,intermedi,avanzati,esperti',
            'category_id' => 'required|exists:article_categories,id'
        ]);

        if (!$this->generatorService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'OpenAI non è configurato correttamente.'
            ], 500);
        }

        $result = $this->generatorService->generateOutline(
            $validated['topic'],
            $validated['keywords'],
            $validated['target_audience']
        );

        if ($result['success']) {
            // Store outline in session for next steps
            Session::put('article_generation', [
                'step' => 'outline_generated',
                'input_data' => $validated,
                'outline' => $result['outline'],
                'generated_at' => now()
            ]);
        }

        return response()->json($result);
    }

    /**
     * Preview the generated outline.
     */
    public function previewOutline(): JsonResponse
    {
        $sessionData = Session::get('article_generation');
        
        if (!$sessionData || $sessionData['step'] !== 'outline_generated') {
            return response()->json([
                'success' => false,
                'error' => 'Nessun outline trovato. Ricomincio il processo.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'outline' => $sessionData['outline'],
            'input_data' => $sessionData['input_data']
        ]);
    }

    /**
     * Modify the generated outline.
     */
    public function modifyOutline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outline' => 'required|array',
            'outline.title' => 'required|string|max:255',
            'outline.meta_description' => 'required|string|max:500',
            'outline.sections' => 'required|array|min:3',
            'outline.sections.*.heading' => 'required|string|max:255',
            'outline.sections.*.description' => 'required|string|max:500',
            'outline.sections.*.word_count' => 'required|integer|min:50|max:500'
        ]);

        $sessionData = Session::get('article_generation');
        
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'error' => 'Sessione scaduta. Ricomincio il processo.'
            ], 404);
        }

        // Update outline in session
        $sessionData['outline'] = $validated['outline'];
        $sessionData['step'] = 'outline_modified';
        $sessionData['modified_at'] = now();
        
        Session::put('article_generation', $sessionData);

        return response()->json([
            'success' => true,
            'message' => 'Outline modificato con successo!'
        ]);
    }

    /**
     * Generate the complete article from the outline.
     */
    public function generateArticle(): JsonResponse
    {
        $sessionData = Session::get('article_generation');
        
        if (!$sessionData || !in_array($sessionData['step'], ['outline_generated', 'outline_modified'])) {
            return response()->json([
                'success' => false,
                'error' => 'Nessun outline valido trovato.'
            ], 404);
        }

        $result = $this->generatorService->generateCompleteArticle(
            $sessionData['outline'],
            $sessionData['input_data']['keywords']
        );

        if ($result['success']) {
            // Store complete article in session
            $sessionData['step'] = 'article_generated';
            $sessionData['article'] = $result['article'];
            $sessionData['completed_at'] = now();
            
            Session::put('article_generation', $sessionData);
        }

        return response()->json($result);
    }

    /**
     * Preview the generated article.
     */
    public function previewArticle(): JsonResponse
    {
        $sessionData = Session::get('article_generation');
        
        if (!$sessionData || $sessionData['step'] !== 'article_generated') {
            return response()->json([
                'success' => false,
                'error' => 'Nessun articolo generato trovato.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'article' => $sessionData['article'],
            'input_data' => $sessionData['input_data']
        ]);
    }

    /**
     * Export the generated article data for manual creation.
     */
    public function exportArticle(): JsonResponse
    {
        $sessionData = Session::get('article_generation');
        
        if (!$sessionData || $sessionData['step'] !== 'article_generated') {
            return response()->json([
                'success' => false,
                'error' => 'Nessun articolo da esportare.'
            ], 404);
        }

        $article = $sessionData['article'];
        $inputData = $sessionData['input_data'];

        // Prepare data for article creation form
        $exportData = [
            'title' => $article['title'],
            'excerpt' => $article['excerpt'],
            'content' => $article['content'],
            'meta_title' => $article['meta_title'],
            'meta_description' => $article['meta_description'],
            'category_id' => $inputData['category_id'],
            'tags' => $inputData['keywords'],
            'word_count' => $article['word_count'],
            'seo_data' => $article['seo_data'],
            'generated_at' => $sessionData['completed_at']
        ];

        return response()->json([
            'success' => true,
            'export_data' => $exportData,
            'create_url' => route('admin.articles.create')
        ]);
    }

    /**
     * Clear the article generation session.
     */
    public function clearSession(): JsonResponse
    {
        Session::forget('article_generation');
        
        return response()->json([
            'success' => true,
            'message' => 'Sessione pulita. Puoi iniziare una nuova generazione.'
        ]);
    }

    /**
     * Get generation progress status.
     */
    public function getStatus(): JsonResponse
    {
        $sessionData = Session::get('article_generation');
        
        if (!$sessionData) {
            return response()->json([
                'success' => true,
                'status' => 'not_started',
                'step' => null
            ]);
        }

        $steps = [
            'outline_generated' => 1,
            'outline_modified' => 2,
            'article_generated' => 3
        ];

        return response()->json([
            'success' => true,
            'status' => 'in_progress',
            'step' => $sessionData['step'],
            'progress' => $steps[$sessionData['step']] ?? 0,
            'total_steps' => 3,
            'created_at' => $sessionData['generated_at'] ?? null
        ]);
    }
}
