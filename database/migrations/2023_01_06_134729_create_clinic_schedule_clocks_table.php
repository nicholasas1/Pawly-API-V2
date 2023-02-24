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
        Schema::create('clinic_schedule_clocks', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->char("schedule_id",10)->nullable();
            $table->time("start_hour")->nullable();
            $table->time("end_hour")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_schedule_clocks');
    }
};
