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
        // 1. Přejmenování amount -> total_amount (pokud je potřeba)
        if (Schema::hasColumn('payments', 'amount') && !Schema::hasColumn('payments', 'total_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('amount', 'total_amount');
            });
        }

        // 2. Přidání nových sloupců (pokud neexistují)
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payout_amount')) {
                // Použijeme after total_amount (předpokládáme, že už existuje)
                $table->unsignedInteger('payout_amount')->nullable()->after('total_amount');
            }
            
            if (!Schema::hasColumn('payments', 'fee_amount')) {
                $table->unsignedInteger('fee_amount')->nullable()->after('total_amount');
            }
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