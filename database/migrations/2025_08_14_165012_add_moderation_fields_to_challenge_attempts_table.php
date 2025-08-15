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
        Schema::table('challenge_attempts', function (Blueprint $table) {
            // Campi per moderazione admin
            $table->timestamp('reviewed_at')->nullable()->after('flagged_for_review')->comment('Timestamp revisione moderatore');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete()->comment('ID admin che ha revisionato');
            $table->text('admin_notes')->nullable()->after('reviewed_by')->comment('Note admin sulla decisione di moderazione');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_attempts', function (Blueprint $table) {
            $table->dropColumn(['reviewed_at', 'reviewed_by', 'admin_notes']);
        });
    }
};
