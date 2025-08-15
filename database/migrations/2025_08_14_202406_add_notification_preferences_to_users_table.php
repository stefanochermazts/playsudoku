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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_new_challenges')->default(true)->after('updated_at')
                  ->comment('Ricevi notifiche email per nuove sfide');
            $table->boolean('notify_weekly_challenges')->default(false)->after('notify_new_challenges')
                  ->comment('Ricevi notifiche email per sfide settimanali (solo utenti attivi)');
            $table->timestamp('last_notification_sent_at')->nullable()->after('notify_weekly_challenges')
                  ->comment('Ultima notifica inviata (per rate limiting)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_new_challenges',
                'notify_weekly_challenges', 
                'last_notification_sent_at'
            ]);
        });
    }
};
