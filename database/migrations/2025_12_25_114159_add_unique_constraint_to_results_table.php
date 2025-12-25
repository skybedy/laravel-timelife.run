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
        Schema::table('results', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate results from webhook
            // Combination of registration_id + finish_time_date + finish_time_sec ensures
            // a user cannot have two identical results (same time) on the same day for the same race
            $table->unique(['registration_id', 'finish_time_date', 'finish_time_sec'], 'results_unique_constraint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropUnique('results_unique_constraint');
        });
    }
};
