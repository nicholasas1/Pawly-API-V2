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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->autoIncrement();
            $table->char('username',100);
            $table->char('email',100);
            $table->char('nickname',100);
            $table->char('fullname',100);
            $table->char('password',100);
            $table->char('phone_number',15)->nullable();;
            $table->date('birthday')->nullable();;
            $table->char('gender',2)->nullable();;
            $table->char('profile_picture',100)->nullable();;
            $table->char('status',20);
            $table->char('sosmed_login',50)->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
