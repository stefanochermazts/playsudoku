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
        Schema::create('public_puzzles', function (Blueprint $table) {
            $table->id();
            
            // Puzzle identification and data
            $table->string('hash', 64)->unique()->comment('SHA-256 hash of puzzle grid');
            $table->json('grid_data')->comment('Original puzzle grid (9x9 array)');
            $table->json('solution_data')->nullable()->comment('Solved puzzle grid (9x9 array)');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert', 'evil'])->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            
            // SEO metadata
            $table->string('seo_title')->nullable()->comment('Auto-generated SEO title');
            $table->text('seo_description')->nullable()->comment('Auto-generated meta description');
            $table->string('seo_keywords')->nullable()->comment('Auto-generated keywords');
            $table->string('canonical_url')->nullable()->comment('Canonical URL for this puzzle');
            
            // Solver results
            $table->json('solver_steps')->nullable()->comment('Step-by-step solution explanation');
            $table->json('techniques_used')->nullable()->comment('Array of techniques used to solve');
            $table->integer('solving_time_ms')->nullable()->comment('Time taken by solver in milliseconds');
            $table->boolean('is_solvable')->default(false)->comment('Whether puzzle is solvable logically');
            
            // Analytics and tracking
            $table->unsignedInteger('view_count')->default(0)->comment('Number of page views');
            $table->unsignedInteger('share_count')->default(0)->comment('Number of social shares');
            $table->timestamp('last_viewed_at')->nullable()->comment('Last time puzzle was viewed');
            $table->timestamp('processed_at')->nullable()->comment('When solver processing completed');
            
            // Foreign keys and user attribution
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('submitted_from_ip')->nullable()->comment('IP address of submitter');
            $table->string('user_agent')->nullable()->comment('User agent of submitter');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['difficulty', 'view_count']);
            $table->index(['is_solvable', 'processed_at']);
            $table->index('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_puzzles');
    }
};
