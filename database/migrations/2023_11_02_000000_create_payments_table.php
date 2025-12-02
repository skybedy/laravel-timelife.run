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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('donor_email', 255)->nullable();
                $table->string('donor_name', 255)->nullable();
                $table->unsignedInteger('amount');
                $table->unsignedInteger('event_id');
                $table->unsignedInteger('payment_recipient_id');
                $table->string('stripe_session_id', 100)->nullable();
                $table->string('stripe_payment_intent_id', 100)->nullable()
                    ->comment('Unique ID sent to recipient for donor identification');
                $table->timestamps();

                $table->index('donor_email');
                $table->index('stripe_payment_intent_id', 'payments_payment_reference_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
