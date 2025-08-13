<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (!Schema::hasColumn('challenge_attempts', 'started_at')) {
				$table->timestamp('started_at')->nullable()->index()->after('user_id')->comment('Inizio tentativo (server time)');
			}
			if (!Schema::hasColumn('challenge_attempts', 'last_activity_at')) {
				$table->timestamp('last_activity_at')->nullable()->after('started_at')->comment('Ultima attività utente');
			}
			if (!Schema::hasColumn('challenge_attempts', 'pause_started_at')) {
				$table->timestamp('pause_started_at')->nullable()->after('last_activity_at')->comment('Inizio pausa corrente');
			}
			if (!Schema::hasColumn('challenge_attempts', 'paused_ms_total')) {
				$table->unsignedBigInteger('paused_ms_total')->default(0)->after('pause_started_at')->comment('Somma pause in millisecondi');
			}
		});
	}

	public function down(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (Schema::hasColumn('challenge_attempts', 'paused_ms_total')) {
				$table->dropColumn('paused_ms_total');
			}
			if (Schema::hasColumn('challenge_attempts', 'pause_started_at')) {
				$table->dropColumn('pause_started_at');
			}
			if (Schema::hasColumn('challenge_attempts', 'last_activity_at')) {
				$table->dropColumn('last_activity_at');
			}
			if (Schema::hasColumn('challenge_attempts', 'started_at')) {
				$table->dropColumn('started_at');
			}
		});
	}
};


