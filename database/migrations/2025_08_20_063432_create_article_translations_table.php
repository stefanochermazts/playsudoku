<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->string('locale', 5)->comment('Language code (it, en, de, es)');
            
            // Content fields
            $table->string('title')->comment('Article title in this language');
            $table->text('excerpt')->nullable()->comment('Short article summary');
            $table->longText('content')->comment('Full article content');
            
            // SEO fields
            $table->string('meta_title')->nullable()->comment('SEO meta title');
            $table->text('meta_description')->nullable()->comment('SEO meta description');
            $table->json('meta_keywords')->nullable()->comment('SEO keywords array');
            
            // Translation tracking
            $table->enum('translation_status', ['pending', 'auto_translated', 'human_reviewed', 'approved'])
                  ->default('pending')
                  ->comment('Translation quality status');
            $table->foreignId('translated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('translated_at')->nullable()->comment('Translation completion timestamp');
            
            // Quality and versioning
            $table->text('translation_notes')->nullable()->comment('Translation notes and comments');
            $table->integer('word_count')->nullable()->comment('Content word count');
            $table->decimal('translation_quality_score', 3, 2)->nullable()->comment('AI translation quality score');
            
            $table->timestamps();
            
            // Composite unique constraint
            $table->unique(['article_id', 'locale'], 'article_locale_unique');
            
            // Indexes for performance
            $table->index(['locale', 'translation_status']);
            $table->index(['article_id', 'locale']);
            $table->index('translated_at');
            $table->fullText(['title', 'excerpt', 'content'], 'article_translations_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_translations');
    }
};