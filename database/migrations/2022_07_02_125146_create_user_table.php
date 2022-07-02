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
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('username');
            $table->text('email');
            $table->text('nickname');
            $table->text('fullname');
            $table->text('phone_number');
            $table->date('birthday');
            $table->text('gender');
            $table->text('profile_picture');
            $table->text('status');
            $table->text('sosmed_login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
};
