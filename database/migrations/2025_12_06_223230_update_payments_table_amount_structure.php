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
        Schema::table('payments', function (Blueprint $table) {
            // Přejmenování amount na total_amount
            $table->renameColumn('amount', 'total_amount');
            
            // Přidání nových sloupců
            $table->unsignedInteger('payout_amount')->nullable()->after('total_amount');
            $table->unsignedInteger('fee_amount')->nullable()->after('payout_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Odstranění nových sloupců
            $table->dropColumn(['payout_amount', 'fee_amount']);
            
            // Zpětné přejmenování
            $table->renameColumn('total_amount', 'amount');
        });
    }
};