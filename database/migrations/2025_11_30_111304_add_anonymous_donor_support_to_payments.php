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
            // Změnit user_id na nullable - pro anonymní dárce
            $table->unsignedInteger('user_id')->nullable()->change();

            // Přidat sloupce pro anonymní dárce
            $table->string('donor_email', 255)->nullable()->after('user_id');
            $table->string('donor_name', 255)->nullable()->after('donor_email');

            // Přidat payment_reference_id - pro referenci příjemci
            $table->string('payment_reference_id', 100)->nullable()->after('stripe_session_id')
                ->comment('Unique ID sent to recipient for donor identification');

            // Indexy pro rychlé vyhledávání
            $table->index('donor_email');
            $table->index('payment_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['donor_email']);
            $table->dropIndex(['payment_reference_id']);

            $table->dropColumn(['donor_email', 'donor_name', 'payment_reference_id']);

            // Varování: Toto může selhat pokud už existují NULL hodnoty
            $table->unsignedInteger('user_id')->nullable(false)->change();
        });
    }
};
