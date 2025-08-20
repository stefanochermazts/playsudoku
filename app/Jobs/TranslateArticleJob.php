<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Services\OpenAITranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job to handle article translation in the background.
 * 
 * Translates an article from Italian to all supported languages
 * using the OpenAI Translation Service.
 */
class TranslateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The article to translate.
     */
    public Article $article;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
        $this->onQueue('translations'); // Use dedicated queue for translations
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAITranslationService $translationService): void
    {
        Log::info('Starting article translation', [
            'article_id' => $this->article->id,
            'article_slug' => $this->article->slug,
            'attempt' => $this->attempts()
        ]);

        try {
            // Check if OpenAI is properly configured
            if (!$translationService->isConfigured()) {
                throw new Exception('OpenAI is not properly configured');
            }

            // Get Italian translation (source)
            $italianTranslation = $this->article->italianTranslation();
            if (!$italianTranslation) {
                throw new Exception('No Italian translation found for article: ' . $this->article->slug);
            }

            // Perform translations
            $results = $translationService->translateArticle($this->article);

            // Log results
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($results as $locale => $result) {
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                    Log::warning('Translation failed for locale', [
                        'article_id' => $this->article->id,
                        'locale' => $locale,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            }

            // Update article translation status
            $this->updateArticleTranslationStatus($successCount, $failureCount);

            Log::info('Article translation completed', [
                'article_id' => $this->article->id,
                'successes' => $successCount,
                'failures' => $failureCount,
                'total_attempts' => $this->attempts()
            ]);

            // If we have failures but some successes, don't fail the job
            if ($failureCount > 0 && $successCount > 0) {
                Log::warning('Partial translation success', [
                    'article_id' => $this->article->id,
                    'successes' => $successCount,
                    'failures' => $failureCount
                ]);
            }

        } catch (Exception $e) {
            Log::error('Article translation job failed', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
            ]);

            // If this is the last attempt, mark as failed
            if ($this->attempts() >= $this->tries) {
                $this->updateArticleTranslationStatus(0, 3, 'failed');
            }

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::error('Article translation job permanently failed', [
            'article_id' => $this->article->id,
            'error' => $exception?->getMessage(),
            'total_attempts' => $this->attempts()
        ]);

        // Update article to reflect translation failure
        $this->updateArticleTranslationStatus(0, 3, 'failed');
    }

    /**
     * Update the article's translation status.
     */
    private function updateArticleTranslationStatus(int $successCount, int $failureCount, string $status = null): void
    {
        $totalLanguages = 3; // en, de, es
        $translationStatus = $this->article->translation_status ?? [];

        // Update status for each language
        $targetLanguages = ['en', 'de', 'es'];
        foreach ($targetLanguages as $locale) {
            if ($status === 'failed') {
                $translationStatus[$locale] = 'failed';
            } elseif ($this->article->hasTranslation($locale)) {
                $translationStatus[$locale] = 'completed';
            } else {
                $translationStatus[$locale] = 'pending';
            }
        }

        // Calculate overall completion
        $completionPercentage = round(($successCount / $totalLanguages) * 100);
        
        $this->article->update([
            'translation_status' => $translationStatus,
            // You could add a completion_percentage field if needed
        ]);

        Log::info('Updated article translation status', [
            'article_id' => $this->article->id,
            'translation_status' => $translationStatus,
            'completion_percentage' => $completionPercentage
        ]);
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            // Add rate limiting middleware if needed
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 180, 300]; // 1 min, 3 min, 5 min
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2); // Don't retry after 2 hours
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'translation',
            'article:' . $this->article->id,
            'openai'
        ];
    }
}