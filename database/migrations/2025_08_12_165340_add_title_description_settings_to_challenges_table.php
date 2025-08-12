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
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('title')->nullable()->after('type')->comment('Titolo della sfida');
            $table->text('description')->nullable()->after('title')->comment('Descrizione della sfida');
            $table->json('settings')->nullable()->after('created_by')->comment('Impostazioni della sfida (hints, time limit, ecc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'settings']);
        });
    }
};