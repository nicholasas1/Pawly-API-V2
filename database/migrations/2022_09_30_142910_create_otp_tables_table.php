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
        Schema::create('otp_tables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('user_id',10);
            $table->char('otp',100);
            $table->char('phone_number',14);
            $table->double('valid_until');
            $table->char('created_at',100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_tables');
    }
};
