<?php

declare(strict_types=1);

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * OpenAI Translation Service
 * 
 * Handles automatic translation of articles from Italian to other languages
 * using OpenAI's GPT models.
 */
class OpenAITranslationService
{
    /**
     * Target languages for translation.
     */
    private const TARGET_LANGUAGES = [
        'en' => 'English',
        'de' => 'German', 
        'es' => 'Spanish'
    ];

    /**
     * Language-specific translation prompts.
     */
    private const LANGUAGE_PROMPTS = [
        'en' => 'You are a professional translator specializing in technical content about Sudoku puzzles and competitive gaming. Translate the following Italian text into natural, fluent English. Maintain the technical accuracy, tone, and formatting. Keep any HTML tags intact.',
        'de' => 'Du bist ein professioneller Übersetzer, der sich auf technische Inhalte über Sudoku-Rätsel und Wettkampfspiele spezialisiert hat. Übersetze den folgenden italienischen Text in natürliches, flüssiges Deutsch. Behalte die technische Genauigkeit, den Ton und die Formatierung bei. Lasse alle HTML-Tags intakt.',
        'es' => 'Eres un traductor profesional especializado en contenido técnico sobre puzzles de Sudoku y juegos competitivos. Traduce el siguiente texto italiano al español natural y fluido. Mantén la precisión técnica, el tono y el formato. Conserva todas las etiquetas HTML intactas.'
    ];

    /**
     * Maximum tokens for translation requests.
     */
    private const MAX_TOKENS = 4000;

    /**
     * Temperature for translation (creativity level).
     */
    private const TEMPERATURE = 0.3;

    /**
     * Translate an article to all target languages.
     */
    public function translateArticle(Article $article): array
    {
        $results = [];
        $italianTranslation = $article->italianTranslation();

        if (!$italianTranslation) {
            throw new Exception('No Italian translation found for article: ' . $article->slug);
        }

        foreach (self::TARGET_LANGUAGES as $locale => $language) {
            try {
                $result = $this->translateToLanguage($article, $italianTranslation, $locale);
                $results[$locale] = $result;
                
                Log::info("Translation completed", [
                    'article_id' => $article->id,
                    'locale' => $locale,
                    'success' => $result['success']
                ]);
            } catch (Exception $e) {
                Log::error("Translation failed", [
                    'article_id' => $article->id,
                    'locale' => $locale,
                    'error' => $e->getMessage()
                ]);
                
                $results[$locale] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Translate article content to a specific language.
     */
    public function translateToLanguage(Article $article, ArticleTranslation $source, string $targetLocale): array
    {
        if (!isset(self::TARGET_LANGUAGES[$targetLocale])) {
            throw new Exception("Unsupported target language: {$targetLocale}");
        }

        $existingTranslation = $article->translation($targetLocale);
        
        // Translate each field separately for better results
        $translatedData = [
            'title' => $this->translateText($source->title, $targetLocale, 'title'),
            'excerpt' => $this->translateText($source->excerpt, $targetLocale, 'excerpt'),
            'content' => $this->translateText($source->content, $targetLocale, 'content'),
            'meta_title' => $this->translateText($source->meta_title ?: $source->title, $targetLocale, 'meta_title'),
            'meta_description' => $this->translateText($source->meta_description ?: $source->excerpt, $targetLocale, 'meta_description'),
        ];

        // Calculate quality score based on length preservation and content structure
        $qualityScore = $this->calculateQualityScore($source, $translatedData);

        $translationData = [
            'article_id' => $article->id,
            'locale' => $targetLocale,
            'translation_status' => 'auto_translated',
            'translated_at' => now(),
            'translation_quality_score' => $qualityScore,
            'word_count' => str_word_count(strip_tags($translatedData['content'])),
            'translation_notes' => "Auto-translated via OpenAI GPT-4 on " . now()->format('Y-m-d H:i:s'),
            ...$translatedData
        ];

        if ($existingTranslation) {
            $existingTranslation->update($translationData);
            $translation = $existingTranslation;
        } else {
            $translation = ArticleTranslation::create($translationData);
        }

        return [
            'success' => true,
            'translation' => $translation,
            'quality_score' => $qualityScore,
            'word_count' => $translationData['word_count']
        ];
    }

    /**
     * Translate a single text using OpenAI.
     */
    private function translateText(string $text, string $targetLocale, string $contentType = 'content'): string
    {
        if (empty(trim($text))) {
            return '';
        }

        $prompt = $this->buildPrompt($targetLocale, $contentType);
        $maxTokens = $this->getMaxTokensForContentType($contentType);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $text]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => self::TEMPERATURE,
                'top_p' => 1.0,
                'frequency_penalty' => 0.0,
                'presence_penalty' => 0.0,
            ]);

            $translatedText = $response->choices[0]->message->content ?? '';
            
            if (empty($translatedText)) {
                throw new Exception('Empty response from OpenAI');
            }

            return trim($translatedText);

        } catch (Exception $e) {
            Log::error('OpenAI translation error', [
                'text_preview' => Str::limit($text, 100),
                'target_locale' => $targetLocale,
                'content_type' => $contentType,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Translation failed: " . $e->getMessage());
        }
    }

    /**
     * Build translation prompt for specific language and content type.
     */
    private function buildPrompt(string $targetLocale, string $contentType): string
    {
        $basePrompt = self::LANGUAGE_PROMPTS[$targetLocale];
        
        $contentSpecificInstructions = match($contentType) {
            'title' => ' Focus on creating an engaging, SEO-friendly title that captures the essence of the original.',
            'excerpt' => ' Create a compelling summary that encourages readers to continue reading the full article.',
            'meta_title' => ' Optimize for search engines while keeping it under 60 characters.',
            'meta_description' => ' Create an SEO-optimized description under 160 characters that includes relevant keywords.',
            'content' => ' Preserve all formatting, technical terms, and maintain the informative yet engaging tone suitable for Sudoku enthusiasts.',
            default => ''
        };

        return $basePrompt . $contentSpecificInstructions;
    }

    /**
     * Get maximum tokens based on content type.
     */
    private function getMaxTokensForContentType(string $contentType): int
    {
        return match($contentType) {
            'title' => 100,
            'excerpt' => 300,
            'meta_title' => 80,
            'meta_description' => 200,
            'content' => self::MAX_TOKENS,
            default => self::MAX_TOKENS
        };
    }

    /**
     * Calculate translation quality score.
     */
    private function calculateQualityScore(ArticleTranslation $source, array $translatedData): float
    {
        $scores = [];

        // Length preservation score (should be within reasonable range)
        $originalLength = strlen($source->content);
        $translatedLength = strlen($translatedData['content']);
        
        if ($originalLength > 0) {
            $lengthRatio = $translatedLength / $originalLength;
            $lengthScore = $lengthRatio >= 0.7 && $lengthRatio <= 1.5 ? 1.0 : 0.7;
            $scores[] = $lengthScore;
        }

        // Title preservation score
        if (!empty($source->title) && !empty($translatedData['title'])) {
            $titleScore = strlen($translatedData['title']) > 10 ? 1.0 : 0.8;
            $scores[] = $titleScore;
        }

        // Content structure score (check for HTML tags preservation)
        $originalTagCount = substr_count($source->content, '<');
        $translatedTagCount = substr_count($translatedData['content'], '<');
        $structureScore = $originalTagCount > 0 ? 
            min(1.0, $translatedTagCount / $originalTagCount) : 1.0;
        $scores[] = $structureScore;

        // Calculate average score
        $averageScore = count($scores) > 0 ? array_sum($scores) / count($scores) : 0.8;
        
        return round($averageScore, 2);
    }

    /**
     * Check if OpenAI is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty(config('openai.api_key'));
    }

    /**
     * Test OpenAI connection with a simple translation.
     */
    public function testConnection(): array
    {
        try {
            $testText = "Ciao, questo è un test di traduzione.";
            $translated = $this->translateText($testText, 'en', 'title');
            
            return [
                'success' => true,
                'original' => $testText,
                'translated' => $translated
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get supported target languages.
     */
    public function getSupportedLanguages(): array
    {
        return self::TARGET_LANGUAGES;
    }

    /**
     * Estimate translation cost (tokens).
     */
    public function estimateTranslationCost(ArticleTranslation $source): array
    {
        $totalWords = str_word_count(strip_tags($source->content));
        $estimatedTokens = (int) ($totalWords * 1.3); // Rough token estimation
        
        $costPerLanguage = $estimatedTokens * count(self::TARGET_LANGUAGES);
        
        return [
            'word_count' => $totalWords,
            'estimated_tokens_per_language' => $estimatedTokens,
            'total_estimated_tokens' => $costPerLanguage,
            'target_languages' => count(self::TARGET_LANGUAGES)
        ];
    }
}
