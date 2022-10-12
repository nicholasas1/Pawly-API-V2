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
        Schema::create('orderservices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('order_id',16);
            $table->char('service',20);
            $table->char('service_id',10);
            $table->char('type',20);
            $table->char('status',20);
            $table->char('total',20);
            $table->float('diskon',10,2);
            $table->char('coupon_name',25)->nullable();
            $table->float('subtotal',20,2);
            $table->char('payment_method',50)->nullable();
            $table->char('payment_id',20)->nullable();
            $table->char('booking_date',50)->nullable();
            $table->char('payed_at')->nullable();
            $table->char('payed_untill')->nullable();
            $table->char('cancelled_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->char('users_ids',20);
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderservices');
    }
};
