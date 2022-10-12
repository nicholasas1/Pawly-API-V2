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
            $table->char('created_at',50)->nullable();
            $table->char('updated_at',50)->nullable();
            $table->char('partner_user_id',20);
            $table->char('comission',20);
            $table->char('partner_paid_status',20);
            $table->char('partner_paid_ammount',20);
            $table->char('partner_paid_at',20);
            $table->char('refund_at',20);
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
