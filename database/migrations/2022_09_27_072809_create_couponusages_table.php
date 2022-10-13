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
        Schema::create('couponusages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('coupon_name',20);
            $table->char('order_id',20);
            $table->integer('user_id')->nullable();
            $table->char('service',20)->nullable();
            $table->char('type',20)->nullable();
            $table->date('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('couponusages');
    }
};
