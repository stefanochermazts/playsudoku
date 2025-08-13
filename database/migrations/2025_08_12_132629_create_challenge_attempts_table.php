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
        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade')->comment('Riferimento alla sfida');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Utente che ha tentato');
            $table->unsignedBigInteger('duration_ms')->nullable()->comment('Durata in millisecondi (null = non completato)');
            $table->unsignedInteger('errors_count')->default(0)->comment('Numero di errori commessi');
            $table->unsignedInteger('hints_used')->default(0)->comment('Numero di hint utilizzati');
            $table->timestamp('completed_at')->nullable()->index()->comment('Timestamp di completamento');
            $table->boolean('valid')->default(true)->index()->comment('Tentativo valido (no cheating)');
            $table->json('current_state')->nullable()->comment('Snapshot stato corrente griglia');
            $table->json('final_state')->nullable()->comment('Stato finale griglia al completamento');
            $table->timestamps();
            
            // Indici critici per leaderboard (come specificato nei requisiti)
            $table->index(['challenge_id', 'valid', 'duration_ms'], 'idx_leaderboard_main');
            $table->index(['completed_at'], 'idx_leaderboard_time');
            $table->index(['user_id', 'challenge_id'], 'idx_user_attempts');
            
            // Un utente può avere solo un tentativo valido per sfida
            $table->unique(['challenge_id', 'user_id'], 'unique_user_challenge_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
    }
};
