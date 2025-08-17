<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing constraint (if it exists)
        DB::statement('ALTER TABLE user_consents DROP CONSTRAINT IF EXISTS user_consents_consent_type_check');
        
        // Add the new constraint with all consent types
        DB::statement("ALTER TABLE user_consents ADD CONSTRAINT user_consents_consent_type_check CHECK (consent_type IN ('essential', 'analytics', 'marketing', 'contact_form', 'registration', 'privacy_settings', 'newsletter'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint
        DB::statement('ALTER TABLE user_consents DROP CONSTRAINT IF EXISTS user_consents_consent_type_check');
        
        // Restore the original constraint (only with the original 3 types)
        DB::statement("ALTER TABLE user_consents ADD CONSTRAINT user_consents_consent_type_check CHECK (consent_type IN ('essential', 'analytics', 'marketing'))");
    }
};
