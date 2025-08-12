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
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->integer('seed')->index()->comment('Seed per generazione deterministica');
            $table->text('givens')->comment('Griglia iniziale (JSON con posizioni e valori)');
            $table->text('solution')->comment('Soluzione completa della griglia (JSON)');
            $table->enum('difficulty', ['easy', 'normal', 'hard', 'expert', 'crazy'])->index()->comment('Livello di difficoltà');
            $table->timestamps();
            
            // Indici per performance
            $table->unique(['seed', 'difficulty'], 'unique_seed_difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
