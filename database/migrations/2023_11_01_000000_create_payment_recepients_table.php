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
        if (!Schema::hasTable('payment_recepients')) {
            Schema::create('payment_recepients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('url', 100);
                $table->string('logo_name', 100);
                $table->string('account_number', 100);
                $table->string('reference_number', 100)->nullable();
                $table->string('stripe_client_id', 100);
                $table->string('stripe_price_id', 100);

                $table->unique('account_number', 'payment_recipients_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_recepients');
    }
};
