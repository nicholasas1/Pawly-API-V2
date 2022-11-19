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
            $table->integer('users_ids');
            $table->char("doctor_name",100);
            $table->longText("description")->nullable();
            $table->longText('address')->nullable();
            $table->longText("Biography")->nullable();
            $table->longText("Education_experience")->nullable();
            $table->char("profile_picture",250)->nullable();
            $table->year("graduated_since")->nullable();
            $table->char("graduated_from",100)->nullable();
            $table->year('worked_since')->nullable();
            $table->char('lat',20)->nullable();
            $table->char('long',20)->nullable();
            $table->float('vidcall_price')->nullable();
            $table->boolean('vidcall_available')->nullable();
            $table->float('chat_price')->nullable();
            $table->boolean('chat_available')->nullable();
            $table->float('offline_price')->nullable();
            $table->boolean('offline_available')->nullable();
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
