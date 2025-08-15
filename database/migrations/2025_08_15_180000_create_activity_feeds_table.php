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
        Schema::create('activity_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')
                ->comment('Utente che ha generato l\'attività');
            $table->enum('type', [
                'challenge_completed', 
                'new_personal_record', 
                'streak_milestone',
                'badge_earned',
                'friend_added',
                'club_joined'
            ])->comment('Tipo di attività');
            $table->json('data')->comment('Dati specifici dell\'attività (challenge_id, time, difficulty, etc.)');
            $table->string('description')->comment('Descrizione human-readable dell\'attività');
            $table->boolean('is_public')->default(true)
                ->comment('Se l\'attività è visibile agli amici');
            $table->timestamps();
            
            // Indici per performance
            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_feeds');
    }
};
