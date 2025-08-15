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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique(); // URL-friendly identifier
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id'); // Proprietario del club
            $table->enum('visibility', ['public', 'private', 'invite_only'])->default('public');
            $table->string('invite_code', 20)->unique()->nullable(); // Codice per inviti
            $table->integer('max_members')->default(50); // Limite membri
            $table->json('settings')->nullable(); // Impostazioni personalizzate
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            // Indici per performance
            $table->index(['visibility', 'is_active']);
            $table->index(['owner_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
