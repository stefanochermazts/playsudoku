<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (!Schema::hasColumn('challenge_attempts', 'current_state')) {
				$table->json('current_state')->nullable()->after('valid')->comment('Snapshot stato corrente griglia');
			}
			if (!Schema::hasColumn('challenge_attempts', 'final_state')) {
				$table->json('final_state')->nullable()->after('current_state')->comment('Stato finale griglia al completamento');
			}
		});
	}

	public function down(): void
	{
		Schema::table('challenge_attempts', function (Blueprint $table) {
			if (Schema::hasColumn('challenge_attempts', 'final_state')) {
				$table->dropColumn('final_state');
			}
			if (Schema::hasColumn('challenge_attempts', 'current_state')) {
				$table->dropColumn('current_state');
			}
		});
	}
};


