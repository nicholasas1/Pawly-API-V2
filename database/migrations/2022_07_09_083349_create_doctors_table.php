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
        Schema::create('doctors', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->char("name",100);
            $table->longText("description")->nullable();
            $table->char("profile_picture",250)->nullable();
            $table->year("graduated_since")->nullable();
            $table->flaot('vidcall_price')->nullable();
            $table->float('chat_price')->nullable();
            $table->float('offline_price')->nullable();
            $table->char('isonline')->nullable();
            $table->char('lastonline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};
