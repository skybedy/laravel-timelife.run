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
        Schema::create('results', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->date('finish_time_date');
            $table->unsignedMediumInteger('finish_time_order')->nullable();
            $table->time('finish_time')->nullable();
            $table->unsignedInteger('finish_time_sec')->nullable();
            $table->double('finish_distance_km')->nullable();
            $table->double('finish_distance_mile')->nullable();
            $table->string('pace_km')->nullable();
            $table->string('pace_mile')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
