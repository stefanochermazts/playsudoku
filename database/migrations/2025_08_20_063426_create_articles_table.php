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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('article_categories')->onDelete('cascade');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            
            // Article status and metadata
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('featured_image')->nullable()->comment('Main article image path');
            $table->json('tags')->nullable()->comment('Article tags for search and categorization');
            
            // Publishing and reading time
            $table->timestamp('published_at')->nullable()->comment('Publication date and time');
            $table->integer('reading_time_minutes')->nullable()->comment('Estimated reading time in minutes');
            $table->boolean('featured')->default(false)->comment('Featured article flag');
            
            // Author and tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // SEO and analytics
            $table->integer('views_count')->default(0)->comment('Article page views counter');
            $table->timestamp('last_viewed_at')->nullable()->comment('Last view timestamp');
            
            // Translation status
            $table->json('translation_status')->nullable()->comment('Track translation completion status');
            $table->boolean('auto_translate')->default(true)->comment('Enable automatic translation via OpenAI');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['status', 'featured', 'published_at']);
            $table->index(['created_by', 'status']);
            $table->index('slug');
            $table->index('published_at');
            $table->fullText(['slug'], 'articles_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};