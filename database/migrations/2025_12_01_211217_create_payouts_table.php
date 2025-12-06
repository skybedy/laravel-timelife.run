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
        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();

                // Stripe payout ID
                $table->string('stripe_payout_id', 100)->unique();

                // Příjemce platby (payment_recepients)
                $table->unsignedInteger('payment_recipient_id');

                // Částka payoutu v haléřích (Stripe posílá v minor units)
                $table->bigInteger('amount');

                // Měna
                $table->string('currency', 3)->default('czk');

                // Datum kdy peníze dorazí na účet
                $table->timestamp('arrival_date')->nullable();

                // Status payoutu: paid, pending, in_transit, canceled, failed
                $table->string('status', 20);

                // Typ payoutu: bank_account, card
                $table->string('type', 20)->nullable();

                // Popis od Stripe
                $table->text('description')->nullable();

                // Celý JSON response ze Stripe pro debug
                $table->json('stripe_data')->nullable();

                $table->timestamps();

                // Indexy
                $table->index('stripe_payout_id');
                $table->index('payment_recipient_id');
                $table->index('arrival_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
