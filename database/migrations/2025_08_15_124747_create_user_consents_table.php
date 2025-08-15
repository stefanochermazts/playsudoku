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
        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            
            // User association (nullable for guest consents)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Consent details
            $table->enum('consent_type', ['essential', 'analytics', 'marketing']);
            $table->boolean('consent_value'); // true = granted, false = denied
            $table->string('consent_version')->default('1.0'); // Version of privacy policy when consent was given
            
            // Technical data for GDPR compliance
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable(); // For guest tracking
            
            // Consent lifecycle
            $table->timestamp('granted_at')->nullable(); // When consent was granted
            $table->timestamp('withdrawn_at')->nullable(); // When consent was withdrawn
            $table->timestamp('expires_at')->nullable(); // When consent expires (typically 13 months for analytics)
            
            // Additional metadata (JSON)
            $table->json('metadata')->nullable(); // Store additional context (source page, etc.)
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'consent_type']);
            $table->index(['consent_type', 'consent_value']);
            $table->index(['created_at']);
            $table->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_consents');
    }
};
