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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puzzle_id')->constrained()->onDelete('cascade')->comment('Riferimento al puzzle');
            $table->enum('type', ['daily', 'weekly', 'custom'])->index()->comment('Tipo di sfida');
            $table->timestamp('starts_at')->index()->comment('Inizio sfida');
            $table->timestamp('ends_at')->index()->comment('Fine sfida');
            $table->enum('visibility', ['public', 'private', 'friends'])->default('public')->comment('Visibilità sfida');
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled')->index()->comment('Stato sfida');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->comment('Creatore della sfida');
            $table->timestamps();
            
            // Indici per performance
            $table->index(['type', 'status', 'starts_at'], 'idx_challenge_listing');
            $table->index(['ends_at', 'status'], 'idx_challenge_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
