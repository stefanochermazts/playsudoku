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
        Schema::create('club_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['owner', 'admin', 'member'])->default('member');
            $table->enum('status', ['active', 'invited', 'banned'])->default('active');
            $table->timestamp('joined_at')->nullable(); // Quando si è unito al club
            $table->timestamp('invited_at')->nullable(); // Quando è stato invitato
            $table->unsignedBigInteger('invited_by')->nullable(); // Chi ha inviato l'invito
            $table->text('invite_message')->nullable(); // Messaggio di invito
            $table->timestamps();

            // Foreign keys
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');

            // Indici per performance
            $table->index(['club_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['role']);
            $table->index(['joined_at']);

            // Vincolo unicità: un utente può essere membro di un club una sola volta
            $table->unique(['club_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_members');
    }
};
