<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (!Schema::hasColumn('challenge_attempts', 'pauses_count')) {
				$table->unsignedInteger('pauses_count')->default(0)->after('paused_ms_total')->comment('Numero di pause effettuate');
			}
		});
	}

	public function down(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (Schema::hasColumn('challenge_attempts', 'pauses_count')) {
				$table->dropColumn('pauses_count');
			}
		});
	}
};


