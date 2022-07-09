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
        Schema::create('clinic_schedules', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->char("clinic_id",100);
            $table->char("doctor_id",100);
            $table->date("day");
            $table->char("status",100);
            $table->time("start_hour");
            $table->time("end_hour");
            $table->longText("description");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_schedules');
    }
};
