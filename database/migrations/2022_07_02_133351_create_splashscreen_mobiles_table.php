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
        Schema::create('splashscreen_mobiles', function (Blueprint $table) {
            $table->increments('id')->autoIncrement();;
            $table->char('meta_name', 15)->nullable();
            $table->char('meta_value', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('splashscreen_mobiles');
    }
};
