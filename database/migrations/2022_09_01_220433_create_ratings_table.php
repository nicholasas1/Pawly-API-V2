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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->integer('service_id')->nullable();
            $table->integer('service_meta')->nullable();
            $table->char('booking_id',15)->nullable();
            $table->integer('users_id');
            $table->integer('ratings')->nullable();
            $table->text('reviews')->nullable();
            $table->char('timereviewed',100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
