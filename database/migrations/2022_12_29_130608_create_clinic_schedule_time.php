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
        Schema::create('clinic_schedule_time', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->time("schedule_id");
            $table->time("start_hour");
            $table->time("end_hour");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_schedule_time');
    }
};
