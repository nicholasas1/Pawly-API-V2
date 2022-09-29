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
        Schema::create('paymentmeths', function (Blueprint $table) {
            $table->id();
            $table->char('service',20)->nullable();
            $table->char('allowed_payment',20)->nullable();
            $table->date('payment_at')->nullable();
            $table->float('payment_amount',20,2)->nullable();
            $table->char('payment_use',20)->nullable();
            $table->char('payment_id',20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paymentmeths');
    }
};
