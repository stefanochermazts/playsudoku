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
        Schema::create('attempt_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('challenge_attempts')->onDelete('cascade')->comment('Riferimento al tentativo');
            $table->unsignedInteger('move_index')->comment('Indice sequenziale della mossa (per replay ordinato)');
            $table->json('payload_json')->comment('Dati della mossa (tipo, coordinate, valore, timestamp, ecc.)');
            $table->timestamps();
            
            // Indici per replay performance
            $table->index(['attempt_id', 'move_index'], 'idx_replay_sequence');
            $table->unique(['attempt_id', 'move_index'], 'unique_attempt_move_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_moves');
    }
};
