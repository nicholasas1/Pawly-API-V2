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
        Schema::create('clinics', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->char("user_id");
            $table->char("clinic_name",100);
            $table->longText("address");
            $table->float('long')->nullable();
            $table->float('lat')->nullable();
            $table->longText("description")->nullable();
            $table->char("clinic_photo",250)->nullable();
            $table->time("opening_hour")->nullable();
            $table->time("close_hour")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinics');
    }
};
