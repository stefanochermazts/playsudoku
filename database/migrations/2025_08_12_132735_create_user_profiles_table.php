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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade')->comment('Riferimento all\'utente');
            $table->string('country', 2)->nullable()->index()->comment('Codice paese ISO (IT, EN, ecc.)');
            $table->json('preferences_json')->nullable()->comment('Preferenze utente (tema, lingua, notifiche, ecc.)');
            $table->timestamps();
            
            // Indici per query geografiche e preferenze
            $table->index(['country'], 'idx_country_stats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
