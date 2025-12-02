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
        Schema::create('track_points', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('registration_id')->nullable();
            $table->unsignedBigInteger('result_id')->nullable();
            $table->double('latitude');
            $table->double('longitude');
            $table->unsignedBigInteger('time')->nullable();
            $table->unsignedInteger('cadence')->nullable();
            $table->double('altitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_points');
    }
};
