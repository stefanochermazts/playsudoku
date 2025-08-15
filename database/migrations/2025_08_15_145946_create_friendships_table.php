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
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Chi invia la richiesta
            $table->unsignedBigInteger('friend_id'); // Chi riceve la richiesta  
            $table->enum('status', ['pending', 'accepted', 'blocked', 'declined'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->text('message')->nullable(); // Messaggio opzionale con la richiesta
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('friend_id')->references('id')->on('users')->onDelete('cascade');

            // Indici per performance
            $table->index(['user_id', 'status']);
            $table->index(['friend_id', 'status']);
            $table->index(['status', 'created_at']);

            // Vincolo unicità: una sola amicizia per coppia di utenti
            $table->unique(['user_id', 'friend_id']);

            // Nota: Check constraint user_id != friend_id gestito a livello applicazione
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
