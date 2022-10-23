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
        Schema::create('vidcalldetails', function (Blueprint $table) {
            $table->char('booking_id',16)->primary();
            $table->text('link_partner')->nullable();
            $table->text('link_user')->nullable();
            $table->char('partner_join_time',50)->nullable();
            $table->char('user_join_time',50)->nullable();
            $table->char('session_done_time',50)->nullable();
            $table->char('session_done_until',50)->nullable();
            $table->char('created_at',50)->nullable();
            $table->char('updated_at',50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vidcalldetails');
    }
};
