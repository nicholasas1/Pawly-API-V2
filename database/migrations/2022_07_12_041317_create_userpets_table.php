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
            $table->text('user_id',100);
            $table->text('petsname',100);
            $table->text('pets_picture',100)->nullable();
            $table->text('species',100);
            $table->text('breed',100);
            $table->text('size',100)->nullable();
            $table->text('gender',10);
            $table->date('birthdate');
            $table->text('neutered',5)->nullable();
            $table->text('vaccinated',5)->nullable();
            $table->text('fdlwdogs',5)->nullable(); //friendly with dogs
            $table->text('fdlwcats',5)->nullable(); //friendly with cats
            $table->text('fdlywkidsless10',5)->nullable(); //friendly with kids < 10 years old
            $table->text('fdlwkidsmore10',5)->nullable(); //friendly with kids > 10 years old
            $table->text('microchipped',5)->nullable();
            $table->text('purbered',5)->nullable();
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
