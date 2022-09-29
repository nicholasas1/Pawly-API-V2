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
            $table->char('service',20);
            $table->char('service_id',10);
            $table->char('type',20);
            $table->char('status',20);
            $table->char('total',20);
            $table->float('diskon',5,2);
            $table->char('coupon_name',25);
            $table->float('subtotal',20,2);
            $table->char('payed_at')->nullable();
            $table->char('payed_untill')->nullable();
            $table->char('cancelled_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->char('users_ids',20);
            $table->timestamps();
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
