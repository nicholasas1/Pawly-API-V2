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
        Schema::create('servicefavourites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('usersids',10);
            $table->char('service_meta',100);
            $table->char('service_id',100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicefavourites');
    }
};
