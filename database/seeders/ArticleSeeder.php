<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating example articles to test the editorial system.
 */
class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $newsCategory = ArticleCategory::where('slug', 'news')->first();
        $techniquesCategory = ArticleCategory::where('slug', 'techniques')->first();
        
        if (!$newsCategory || !$techniquesCategory) {
            $this->command->error('Categories not found. Run ArticleCategorySeeder first.');
            return;
        }

        // Get first admin user
        $admin = User::where('role', 'admin')->first() ?? 
                 User::where('role', 'super_admin')->first() ??
                 User::first();

        if (!$admin) {
            $this->command->error('No admin user found. Create a user first.');
            return;
        }

        $this->createNewsArticles($newsCategory, $admin);
        $this->createTechniqueArticles($techniquesCategory, $admin);

        $this->command->info('🎉 Article seeding completed successfully!');
    }

    private function createNewsArticles($newsCategory, $admin): void
    {
        $article = Article::create([
            'category_id' => $newsCategory->id,
            'slug' => 'nuovo-sistema-classifiche-stagionali',
            'status' => 'published',
            'featured' => true,
            'tags' => ['aggiornamenti', 'classifiche'],
            'published_at' => now()->subDays(2),
            'reading_time_minutes' => 3,
            'created_by' => $admin->id,
        ]);

        ArticleTranslation::create([
            'article_id' => $article->id,
            'locale' => 'it',
            'title' => 'Nuovo Sistema di Classifiche Stagionali',
            'excerpt' => 'Scopri il nuovo sistema di classifiche stagionali che premia la costanza.',
            'content' => '<p>Siamo entusiasti di annunciare il lancio del <strong>nuovo sistema di classifiche stagionali</strong> su PlaySudoku!</p>',
            'translation_status' => 'approved',
            'translated_by' => $admin->id,
            'translated_at' => now(),
        ]);

        $this->command->info("✅ Created News article");
    }

    private function createTechniqueArticles($techniquesCategory, $admin): void
    {
        $article = Article::create([
            'category_id' => $techniquesCategory->id,
            'slug' => 'tecnica-naked-singles',
            'status' => 'published',
            'featured' => true,
            'tags' => ['principianti', 'tecnica-base'],
            'published_at' => now()->subDays(1),
            'reading_time_minutes' => 5,
            'created_by' => $admin->id,
        ]);

        ArticleTranslation::create([
            'article_id' => $article->id,
            'locale' => 'it',
            'title' => 'Tecnica Naked Singles: La Base del Sudoku',
            'excerpt' => 'Scopri la tecnica fondamentale per iniziare a risolvere qualsiasi puzzle Sudoku.',
            'content' => '<p>La tecnica <strong>Naked Singles</strong> è la strategia più fondamentale nel Sudoku.</p>',
            'translation_status' => 'approved',
            'translated_by' => $admin->id,
            'translated_at' => now(),
        ]);

        $this->command->info("✅ Created Technique article");
    }
}