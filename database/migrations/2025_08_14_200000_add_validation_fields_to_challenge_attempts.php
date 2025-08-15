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
            $table->boolean('move_validation_passed')->nullable()->after('valid')->comment('Indica se la validazione anti-cheat delle mosse è passata');
            $table->timestamp('validated_at')->nullable()->after('move_validation_passed')->comment('Timestamp validazione anti-cheat');
            $table->text('validation_notes')->nullable()->after('validated_at')->comment('Note sulla validazione per moderazione');
            $table->boolean('flagged_for_review')->default(false)->after('validation_notes')->comment('Flag per revisione manuale admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'move_validation_passed',
                'validated_at', 
                'validation_notes',
                'flagged_for_review'
            ]);
        });
    }
};


