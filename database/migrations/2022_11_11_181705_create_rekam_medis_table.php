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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('order_id',16)->nullable();
            $table->char('pet_id',10)->nullable();
            $table->text('keluhan')->nullable();
            $table->text('penanganan_sementara')->nullable();
            $table->text('penanganan_lanjut')->nullable();
            $table->text('diagnosa')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekam_medis');
    }
};
