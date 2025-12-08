<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spustí změny: Schema::table říká, že upravujeme existující tabulku.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Decimal(10, 2) znamená: celkem 10 číslic, z toho 2 jsou za čárkou.
            // ->change() je klíčové slovo, které říká: "Nemaž sloupec, jen mu změň typ."
            
            $table->decimal('total_amount', 10, 2)->change();
            $table->decimal('fee_amount', 10, 2)->nullable()->change();
            $table->decimal('payout_amount', 10, 2)->nullable()->change();
        });
    }

    /**
     * Vrátí změny zpět: Pokud byste chtěl migraci vzít zpět, vrátí sloupce na Integer.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('total_amount')->change();
            $table->integer('fee_amount')->nullable()->change();
            $table->integer('payout_amount')->nullable()->change();
        });
    }
};