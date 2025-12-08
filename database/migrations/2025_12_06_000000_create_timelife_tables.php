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
        // Categories
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->tinyInteger('order');
                $table->enum('gender', ['M', 'F']);
                $table->tinyInteger('age_start');
                $table->tinyInteger('age_end');
                $table->tinyInteger('open')->nullable();
                $table->timestamps();
            });
        }

        // Events
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('display')->default(1);
                $table->unsignedTinyInteger('event_type_id')->default(1);
                $table->unsignedTinyInteger('platform_id');
                $table->unsignedTinyInteger('serie_id');
                $table->string('name');
                $table->string('second_name')->nullable();
                $table->unsignedInteger('distance');
                $table->unsignedInteger('time')->nullable();
                $table->date('date_start')->nullable();
                $table->date('date_end')->nullable();
                $table->timestamps();
            });
        }

        // Payment Recepients
        if (!Schema::hasTable('payment_recepients')) {
            Schema::create('payment_recepients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('url', 100);
                $table->string('logo_name', 100);
                $table->string('account_number', 100)->unique();
                $table->string('reference_number', 100)->nullable();
                $table->string('stripe_client_id', 100);
                $table->string('stripe_price_id', 100);
            });
        }

        // Payments
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('donor_email', 255)->nullable()->index();
                $table->string('donor_name', 255)->nullable();
                
                $table->unsignedInteger('total_amount');
                $table->unsignedInteger('payout_amount')->nullable();
                $table->unsignedInteger('fee_amount')->nullable();
                
                $table->unsignedInteger('event_id');
                $table->unsignedInteger('payment_recipient_id');
                $table->string('stripe_session_id', 100)->nullable();
                
                $table->string('stripe_payment_intent_id', 100)->nullable()
                    ->comment('Unique ID sent to recipient for donor identification');
                
                $table->timestamps();

                $table->index('stripe_payment_intent_id', 'payments_payment_reference_id_index');
            });
        }

        // Payouts
        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->string('stripe_payout_id', 100)->unique();
                $table->unsignedInteger('payment_recipient_id');
                $table->bigInteger('amount');
                $table->string('currency', 3)->default('czk');
                $table->timestamp('arrival_date')->nullable();
                $table->string('status', 20);
                $table->string('type', 20)->nullable();
                $table->text('description')->nullable();
                $table->json('stripe_data')->nullable();
                $table->timestamps();

                $table->index('stripe_payout_id');
                $table->index('payment_recipient_id');
                $table->index('arrival_date');
            });
        }

        // Registrations (Pozor: bez primary key dle dumpu)
        if (!Schema::hasTable('registrations')) {
            Schema::create('registrations', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('event_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedInteger('ids')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

// Results
if (!Schema::hasTable('results')) {
    Schema::create('results', function (Blueprint $table) {
        // `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT + PRIMARY KEY
        $table->id(); 

        // `registration_id` + FOREIGN KEY s kaskádovým mazáním
        $table->foreignId('registration_id')
              ->constrained('registrations')
              ->onDelete('cascade');

        // Ostatní sloupce podle schématu
        $table->date('finish_time_date');
        $table->unsignedMediumInteger('finish_time_order')->nullable();
        $table->time('finish_time')->nullable();
        $table->unsignedInteger('finish_time_sec');
        $table->double('finish_distance_km')->nullable();
        $table->double('finish_distance_mile')->nullable();
        $table->string('pace_km');
        $table->string('pace_mile');

        // `created_at` a `updated_at`
        $table->timestamps();
    });
}

   // Track Points
if (!Schema::hasTable('track_points')) {
    Schema::create('track_points', function (Blueprint $table) {
        // `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT + PRIMARY KEY
        $table->id();

        // `user_id` int(10) unsigned NOT NULL
        $table->unsignedInteger('user_id');

        // `registration_id` int(10) unsigned DEFAULT NULL
        $table->unsignedInteger('registration_id')->nullable();

        // `result_id` bigint(20) unsigned NOT NULL + FOREIGN KEY
        $table->foreignId('result_id')
              ->constrained('results')
              ->onDelete('cascade'); // přidáno cascade pro integritu, v SQL není explicitně, ale doporučuje se

        $table->double('latitude');
        $table->double('longitude');
        $table->unsignedBigInteger('time')->nullable();
        $table->unsignedInteger('cadence')->nullable();
        $table->double('altitude')->nullable();
        $table->timestamps();

        // UNIQUE KEY `track_points_unique` (`user_id`,`registration_id`,`latitude`,`longitude`,`time`)
        $table->unique(['user_id', 'registration_id', 'latitude', 'longitude', 'time'], 'track_points_unique');
    });
}

        // Sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('track_points');
        Schema::dropIfExists('results');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_recepients');
        Schema::dropIfExists('events');
        Schema::dropIfExists('categories');
    }
};
