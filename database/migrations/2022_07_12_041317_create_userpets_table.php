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
            $table->text('species',100);
            $table->text('breed',100);
            $table->text('size',100)->nullable();
            $table->text('gender',10);
            $table->date('birthdate');
            $table->boolean('neutered')->nullable();
            $table->boolean('vaccinated')->nullable();
            $table->boolean('fdlwdogs')->nullable(); //friendly with dogs
            $table->boolean('fdlwcats')->nullable(); //friendly with cats
            $table->boolean('fdlywkidsless10')->nullable(); //friendly with kids < 10 years old
            $table->boolean('fdlwkidsmore10')->nullable(); //friendly with kids > 10 years old
            $table->boolean('microchipped')->nullable();
            $table->boolean('purbered')->nullable();
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
