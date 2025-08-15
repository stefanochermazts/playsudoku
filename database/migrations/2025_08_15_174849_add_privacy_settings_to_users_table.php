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
            $table->enum('profile_visibility', ['public', 'friends', 'private'])->default('public')
                ->comment('Chi può vedere il profilo utente');
            $table->enum('stats_visibility', ['public', 'friends', 'private'])->default('public')
                ->comment('Chi può vedere le statistiche dell\'utente');
            $table->boolean('friend_requests_enabled')->default(true)
                ->comment('Permette di ricevere richieste di amicizia');
            $table->boolean('show_online_status')->default(true)
                ->comment('Mostra lo status online agli amici');
            $table->boolean('activity_feed_visible')->default(true)
                ->comment('Le proprie attività appaiono nell\'activity feed degli amici');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_visibility',
                'stats_visibility', 
                'friend_requests_enabled',
                'show_online_status',
                'activity_feed_visible'
            ]);
        });
    }
};
