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
        Schema::create('mobile_banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('index')->nullable();
            $table->text('image_url')->nullable();
            $table->char('isactive',10)->nullable();
            $table->text('url')->nullable();
            $table->text('text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_banners');
    }
};
