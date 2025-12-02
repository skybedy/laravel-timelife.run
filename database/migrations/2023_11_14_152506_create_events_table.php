<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('display')->default(1);
            $table->unsignedTinyInteger('event_type_id')->default(1);
            $table->unsignedTinyInteger('platform_id');
            $table->unsignedTinyInteger('serie_id');
            $table->string('name');
            $table->string('second_name')->nullable()->default(null);
            $table->unsignedInteger('distance');
            $table->unsignedInteger('time')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
