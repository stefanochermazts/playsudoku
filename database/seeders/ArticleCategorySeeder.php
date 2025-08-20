<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

/**
 * Seeder for article categories.
 * 
 * Creates the initial categories for the editorial system:
 * - News: Updates about PlaySudoku platform
 * - Techniques: Sudoku solving techniques and strategies
 */
class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'news',
                'name_it' => 'News',
                'name_en' => 'News',
                'name_de' => 'Neuigkeiten',
                'name_es' => 'Noticias',
                'description_it' => 'Novità e aggiornamenti sulla piattaforma PlaySudoku, nuove funzionalità e miglioramenti del sistema competitivo.',
                'description_en' => 'Latest updates and news about the PlaySudoku platform, new features and competitive system improvements.',
                'description_de' => 'Neueste Updates und Nachrichten über die PlaySudoku-Plattform, neue Funktionen und Verbesserungen des Wettkampfsystems.',
                'description_es' => 'Últimas actualizaciones y noticias sobre la plataforma PlaySudoku, nuevas funciones y mejoras del sistema competitivo.',
                'sort_order' => 1,
                'active' => true,
                'icon' => '📰',
                'color' => '#3B82F6', // Blue
            ],
            [
                'slug' => 'techniques',
                'name_it' => 'Tecniche',
                'name_en' => 'Techniques',
                'name_de' => 'Techniken',
                'name_es' => 'Técnicas',
                'description_it' => 'Guide dettagliate sulle tecniche di risoluzione del Sudoku, strategie avanzate e metodologie per migliorare le proprie competenze.',
                'description_en' => 'Detailed guides on Sudoku solving techniques, advanced strategies and methodologies to improve your skills.',
                'description_de' => 'Detaillierte Anleitungen zu Sudoku-Lösungstechniken, fortgeschrittene Strategien und Methoden zur Verbesserung Ihrer Fähigkeiten.',
                'description_es' => 'Guías detalladas sobre técnicas de resolución de Sudoku, estrategias avanzadas y metodologías para mejorar tus habilidades.',
                'sort_order' => 2,
                'active' => true,
                'icon' => '🧩',
                'color' => '#10B981', // Green
            ],
        ];

        foreach ($categories as $categoryData) {
            ArticleCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        $this->command->info('✅ Article categories seeded successfully');
        $this->command->info('Created categories: News, Techniques');
    }
}