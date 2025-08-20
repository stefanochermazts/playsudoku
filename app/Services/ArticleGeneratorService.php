<?php

declare(strict_types=1);

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Article Generator Service
 * 
 * Generates SEO-optimized articles using OpenAI with structured approach:
 * 1. Keywords analysis
 * 2. Outline generation
 * 3. Content expansion
 * 4. SEO optimization
 */
class ArticleGeneratorService
{
    /**
     * Maximum tokens for different generation steps.
     */
    private const MAX_TOKENS = [
        'outline' => 1000,
        'content' => 4000,
        'seo' => 500,
    ];

    /**
     * Temperature settings for different types of content.
     */
    private const TEMPERATURE = [
        'outline' => 0.7,
        'content' => 0.8,
        'seo' => 0.3,
    ];

    /**
     * Generate article outline based on topic and keywords.
     */
    public function generateOutline(string $topic, array $keywords, string $targetAudience = 'principianti'): array
    {
        $keywordsString = implode(', ', $keywords);
        
        $prompt = "Sei un esperto di Sudoku e content marketing specializzato in SEO. 
        
TASK: Crea un outline dettagliato per un articolo di almeno 1000 parole sul tema: \"{$topic}\"

KEYWORDS DA OTTIMIZZARE: {$keywordsString}

TARGET AUDIENCE: {$targetAudience}

REQUIREMENTS:
- Outline strutturato con H1, H2, H3
- Almeno 6-8 sezioni principali
- Ogni sezione deve essere di 150-200 parole
- Integra naturalmente le keywords nell'outline
- Includi esempi pratici e consigli actionable
- Aggiungi call-to-action per PlaySudoku quando appropriato

RESPONSE FORMAT:
```json
{
    \"title\": \"Titolo principale ottimizzato SEO (max 60 caratteri)\",
    \"meta_description\": \"Meta description coinvolgente (max 160 caratteri)\",
    \"introduction\": \"Breve descrizione dell'introduzione (50-100 parole)\",
    \"sections\": [
        {
            \"heading\": \"Titolo sezione\",
            \"description\": \"Cosa coprire in questa sezione\",
            \"keywords\": [\"keyword1\", \"keyword2\"],
            \"word_count\": 150
        }
    ],
    \"conclusion\": \"Descrizione della conclusione\",
    \"cta\": \"Call-to-action per PlaySudoku\"
}
```";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Genera outline per: {$topic}"]
                ],
                'max_tokens' => self::MAX_TOKENS['outline'],
                'temperature' => self::TEMPERATURE['outline'],
            ]);

            $content = $response->choices[0]->message->content ?? '';
            
            // Extract JSON from response
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $jsonData = json_decode($matches[1], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'outline' => $jsonData,
                        'raw_response' => $content
                    ];
                }
            }

            throw new Exception('Invalid JSON response from OpenAI');

        } catch (Exception $e) {
            Log::error('Article outline generation failed', [
                'topic' => $topic,
                'keywords' => $keywords,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Expand a single section of the outline into full content.
     */
    public function expandSection(array $sectionData, array $keywords, string $context = ''): array
    {
        $keywordsString = implode(', ', $keywords);
        
        $prompt = "Sei un esperto di Sudoku che scrive contenuti educativi e coinvolgenti per PlaySudoku.

TASK: Espandi la seguente sezione in un paragrafo completo e ben strutturato.

SEZIONE DA ESPANDERE:
- Titolo: {$sectionData['heading']}
- Descrizione: {$sectionData['description']}
- Parole target: {$sectionData['word_count']}
- Keywords: " . implode(', ', $sectionData['keywords'] ?? []) . "

CONTEXT: {$context}

KEYWORDS PRINCIPALI DA INTEGRARE: {$keywordsString}

GUIDELINES:
- INIZIA SEMPRE con il titolo della sezione come tag H2: <h2>{$sectionData['heading']}</h2>
- Scrivi in italiano naturale e coinvolgente
- Usa HTML per formattazione (h3, p, strong, em, ul, li per il contenuto)
- Integra le keywords in modo naturale
- Includi esempi pratici quando possibile
- Mantieni il tono educativo ma accessibile
- Usa sottotitoli (h3) se la sezione è lunga
- Aggiungi liste puntate per migliorare leggibilità

IMPORTANTE: 
- Inizia SEMPRE con <h2>{$sectionData['heading']}</h2>
- Rispondi SOLO con il contenuto HTML della sezione, senza blocchi di codice (```html) o altre decorazioni markdown

TARGET WORD COUNT: {$sectionData['word_count']} parole";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Espandi questa sezione mantenendo alta qualità e valore per il lettore."]
                ],
                'max_tokens' => self::MAX_TOKENS['content'],
                'temperature' => self::TEMPERATURE['content'],
            ]);

            $content = $response->choices[0]->message->content ?? '';
            
            // Clean AI artifacts from content
            $content = $this->cleanGeneratedContent($content);
            
            $wordCount = str_word_count(strip_tags($content));

            return [
                'success' => true,
                'content' => $content,
                'word_count' => $wordCount
            ];

        } catch (Exception $e) {
            Log::error('Section expansion failed', [
                'section' => $sectionData['heading'] ?? 'Unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate introduction and conclusion for the article.
     */
    public function generateIntroAndConclusion(array $outline, array $keywords): array
    {
        $keywordsString = implode(', ', $keywords);
        
        $prompt = "Sei un copywriter esperto specializzato in contenuti Sudoku per PlaySudoku.

TASK: Scrivi introduzione e conclusione per questo articolo:

TITOLO: {$outline['title']}
DESCRIZIONE: {$outline['meta_description']}
KEYWORDS: {$keywordsString}

SEZIONI PRINCIPALI:
" . implode("\n", array_map(function($section) {
    return "- " . $section['heading'];
}, $outline['sections'])) . "

REQUIREMENTS INTRODUZIONE (100-150 parole):
- Hook coinvolgente che cattura l'attenzione
- Presenta il problema/bisogno del lettore
- Anticipa i benefici dell'articolo
- Integra la keyword principale naturalmente
- Termina con una preview di cosa imparerà

REQUIREMENTS CONCLUSIONE (100-150 parole):
- Riassumi i punti chiave dell'articolo
- Rinforza i benefici per il lettore
- Call-to-action per PlaySudoku (prova la piattaforma)
- Invita all'azione concreta
- Termina con nota motivazionale

RESPONSE FORMAT:
```json
{
    \"introduction\": \"<p>Contenuto introduzione con HTML...</p>\",
    \"conclusion\": \"<p>Contenuto conclusione con HTML...</p>\"
}
```";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Genera introduzione e conclusione coinvolgenti per questo articolo."]
                ],
                'max_tokens' => self::MAX_TOKENS['content'],
                'temperature' => self::TEMPERATURE['content'],
            ]);

            $content = $response->choices[0]->message->content ?? '';
            
            // Extract JSON from response
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $jsonData = json_decode($matches[1], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Clean AI artifacts from introduction and conclusion
                    $introduction = isset($jsonData['introduction']) ? $this->cleanGeneratedContent($jsonData['introduction']) : '';
                    $conclusion = isset($jsonData['conclusion']) ? $this->cleanGeneratedContent($jsonData['conclusion']) : '';
                    
                    return [
                        'success' => true,
                        'introduction' => $introduction,
                        'conclusion' => $conclusion
                    ];
                }
            }

            throw new Exception('Invalid JSON response from OpenAI');

        } catch (Exception $e) {
            Log::error('Intro/conclusion generation failed', [
                'title' => $outline['title'] ?? 'Unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize article for SEO by enhancing meta tags and suggesting improvements.
     */
    public function optimizeSEO(string $title, string $content, array $keywords): array
    {
        $keywordsString = implode(', ', $keywords);
        $contentPreview = Str::limit(strip_tags($content), 500);
        
        $prompt = "Sei un esperto SEO specializzato in ottimizzazione contenuti per blog di gaming/puzzle.

TASK: Ottimizza i meta tag e suggerisci miglioramenti SEO per questo articolo.

TITOLO ATTUALE: {$title}
KEYWORDS TARGET: {$keywordsString}
ANTEPRIMA CONTENUTO: {$contentPreview}

ANALYSIS REQUESTS:
1. Analizza keyword density delle keywords principali
2. Suggerisci title tag ottimizzato (max 60 caratteri)
3. Crea meta description accattivante (max 160 caratteri)
4. Suggerisci miglioramenti per la struttura H2/H3
5. Identifica keywords semantiche correlate da aggiungere

RESPONSE FORMAT:
```json
{
    \"seo_title\": \"Title tag ottimizzato\",
    \"meta_description\": \"Meta description coinvolgente\",
    \"keyword_density\": {
        \"keyword1\": \"2.3%\",
        \"keyword2\": \"1.8%\"
    },
    \"suggestions\": [
        \"Suggerimento 1 per migliorare SEO\",
        \"Suggerimento 2 per migliorare SEO\"
    ],
    \"related_keywords\": [\"keyword correlata 1\", \"keyword correlata 2\"],
    \"seo_score\": 85
}
```";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Analizza e ottimizza questo articolo per la SEO."]
                ],
                'max_tokens' => self::MAX_TOKENS['seo'],
                'temperature' => self::TEMPERATURE['seo'],
            ]);

            $content = $response->choices[0]->message->content ?? '';
            
            // Extract JSON from response
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $jsonData = json_decode($matches[1], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'seo_data' => $jsonData
                    ];
                }
            }

            throw new Exception('Invalid JSON response from OpenAI');

        } catch (Exception $e) {
            Log::error('SEO optimization failed', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate complete article from outline.
     */
    public function generateCompleteArticle(array $outline, array $keywords): array
    {
        try {
            $sections = [];
            $totalWordCount = 0;

            // Generate introduction and conclusion
            $introResult = $this->generateIntroAndConclusion($outline, $keywords);
            if (!$introResult['success']) {
                throw new Exception('Failed to generate introduction and conclusion');
            }

            // Expand each section
            foreach ($outline['sections'] as $sectionData) {
                $sectionResult = $this->expandSection($sectionData, $keywords);
                if (!$sectionResult['success']) {
                    throw new Exception('Failed to expand section: ' . $sectionData['heading']);
                }
                
                $sections[] = [
                    'heading' => $sectionData['heading'],
                    'content' => $sectionResult['content'],
                    'word_count' => $sectionResult['word_count']
                ];
                
                $totalWordCount += $sectionResult['word_count'];
            }

            // Compile complete article
            $completeContent = $introResult['introduction'] . "\n\n";
            
            foreach ($sections as $section) {
                $completeContent .= $section['content'] . "\n\n";
            }
            
            $completeContent .= $introResult['conclusion'];

            // Add word counts
            $totalWordCount += str_word_count(strip_tags($introResult['introduction']));
            $totalWordCount += str_word_count(strip_tags($introResult['conclusion']));

            // Generate SEO optimization
            $seoResult = $this->optimizeSEO($outline['title'], $completeContent, $keywords);

            return [
                'success' => true,
                'article' => [
                    'title' => $outline['title'],
                    'content' => $completeContent,
                    'excerpt' => $outline['meta_description'],
                    'meta_title' => $seoResult['success'] ? $seoResult['seo_data']['seo_title'] : $outline['title'],
                    'meta_description' => $seoResult['success'] ? $seoResult['seo_data']['meta_description'] : $outline['meta_description'],
                    'word_count' => $totalWordCount,
                    'sections' => $sections,
                    'seo_data' => $seoResult['success'] ? $seoResult['seo_data'] : null
                ]
            ];

        } catch (Exception $e) {
            Log::error('Complete article generation failed', [
                'outline_title' => $outline['title'] ?? 'Unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if OpenAI is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty(config('openai.api_key'));
    }

    /**
     * Clean AI-generated content from unwanted HTML artifacts.
     */
    private function cleanGeneratedContent(string $content): string
    {
        // Remove code block markers that sometimes appear in AI responses
        $content = preg_replace('/```html\s*/i', '', $content);
        $content = preg_replace('/```\s*$/m', '', $content);
        $content = preg_replace('/```\s*\n/', '', $content);
        
        // Remove other common AI artifacts
        $content = preg_replace('/```[\w]*\s*/i', '', $content);
        $content = str_replace('```', '', $content);
        
        // Fix div headers that should be h2
        $content = preg_replace('/<div[^>]*>\s*([^<]+?)\s*<\/div>\s*(?=<p|<ul|<ol|<h3)/i', '<h2>$1</h2>', $content);
        
        // Clean up multiple line breaks
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Trim whitespace
        $content = trim($content);
        
        return $content;
    }
}
