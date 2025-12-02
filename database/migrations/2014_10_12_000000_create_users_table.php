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
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('firstname');
                $table->string('lastname');
                $table->string('country');
                $table->string('team')->nullable()->default(null);
                $table->year('birth_year');
                $table->enum('gender', ['M', 'F']);
                $table->string('email')->unique();
                $table->string('password');
                $table->string('facebook_id')->nullable();
                $table->string('google_id')->nullable();
                $table->integer('strava_id')->unsigned()->nullable();
                $table->string('strava_access_token')->nullable();
                $table->string('strava_refresh_token')->nullable();
                $table->integer('strava_expires_at')->unsigned()->nullable();
                $table->string('strava_scope')->nullable();
                $table->tinyInteger('password_changed')->unsigned()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamps();
                $table->string('p', 100)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
