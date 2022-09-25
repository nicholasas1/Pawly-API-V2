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
        Schema::create('userpets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('user_id',100);
            $table->char('petsname',100);
            $table->char('pets_picture',100)->nullable();
            $table->char('species',100);
            $table->char('breed',100);
            $table->char('size',100)->nullable();
            $table->char('gender',10);
            $table->date('birthdate');
            $table->char('neutered',5)->nullable();
            $table->char('vaccinated',5)->nullable();
            $table->char('fdlwdogs',5)->nullable(); //friendly with dogs
            $table->char('fdlwcats',5)->nullable(); //friendly with cats
            $table->char('fdlywkidsless10',5)->nullable(); //friendly with kids < 10 years old
            $table->char('fdlwkidsmore10',5)->nullable(); //friendly with kids > 10 years old
            $table->char('microchipped',5)->nullable();
            $table->char('purbered',5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userpets');
    }
};
