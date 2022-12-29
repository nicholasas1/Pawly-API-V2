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
        Schema::create('clinic_op_cls', function (Blueprint $table) {
            $table->id();
            $table->char('clinic_id',20)->nullable();
            $table->char('day',10)->nullable();
            $table->time('opening_hour')->nullable();
            $table->time('close_hour')->nullable();
            $table->char('status',20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_op_cls');
    }
};
