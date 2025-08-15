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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index()->comment('Tipo di evento (admin_action, security_event, etc.)');
            $table->string('action')->index()->comment('Azione specifica (create_challenge, moderate_attempt, etc.)');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('Utente che ha eseguito l\'azione');
            $table->string('user_email')->nullable()->comment('Email utente per tracciabilità');
            $table->string('user_role')->nullable()->comment('Ruolo utente al momento dell\'azione');
            
            // Dati del target dell'azione
            $table->string('target_type')->nullable()->comment('Tipo di entità target (Challenge, User, etc.)');
            $table->unsignedBigInteger('target_id')->nullable()->comment('ID dell\'entità target');
            $table->json('target_data')->nullable()->comment('Snapshot dei dati target');
            
            // Metadati dell'azione
            $table->json('changes')->nullable()->comment('Cosa è cambiato (before/after)');
            $table->json('metadata')->nullable()->comment('Metadati aggiuntivi (IP, User Agent, etc.)');
            $table->text('description')->comment('Descrizione human-readable dell\'evento');
            
            // Sicurezza e compliance
            $table->string('severity', 20)->default('info')->index()->comment('Livello severità: info, warning, critical');
            $table->string('ip_address', 45)->nullable()->index()->comment('Indirizzo IP dell\'utente');
            $table->string('user_agent')->nullable()->comment('User Agent del browser');
            $table->json('session_data')->nullable()->comment('Dati di sessione rilevanti');
            
            // Timestamp e retention
            $table->timestamp('created_at')->index()->comment('Quando è avvenuto l\'evento');
            $table->date('retention_until')->nullable()->index()->comment('Data fino a cui conservare il log');
            
            // Indici per query performance
            $table->index(['event_type', 'created_at'], 'idx_audit_type_date');
            $table->index(['user_id', 'created_at'], 'idx_audit_user_date');
            $table->index(['target_type', 'target_id'], 'idx_audit_target');
            $table->index(['severity', 'created_at'], 'idx_audit_severity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};