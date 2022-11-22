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
        Schema::create('notificationdbs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('usersids',10);
            $table->char('meta_role',100)->nullable();
            $table->text('meta_id')->nullable();
            $table->text('notification_data')->nullable();
            $table->boolean('view')->nullable();
            $table->text('redirect')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificationdbs');
    }
};
