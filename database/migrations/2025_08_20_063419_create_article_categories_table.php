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
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            
            // Multilingual names
            $table->string('name_it')->comment('Category name in Italian');
            $table->string('name_en')->comment('Category name in English');
            $table->string('name_de')->comment('Category name in German');
            $table->string('name_es')->comment('Category name in Spanish');
            
            // Multilingual descriptions
            $table->text('description_it')->nullable()->comment('Category description in Italian');
            $table->text('description_en')->nullable()->comment('Category description in English');
            $table->text('description_de')->nullable()->comment('Category description in German');
            $table->text('description_es')->nullable()->comment('Category description in Spanish');
            
            // Category settings
            $table->integer('sort_order')->default(0)->comment('Display order in menus');
            $table->boolean('active')->default(true)->comment('Category visibility');
            $table->string('icon')->nullable()->comment('Category icon (CSS class or emoji)');
            $table->string('color')->nullable()->comment('Category color theme');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['active', 'sort_order']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};