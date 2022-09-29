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
        Schema::create('couponservices', function (Blueprint $table) {
            $table->char('coupon_name',20)->primary();
            $table->char('coupon_type',10);
            $table->float('min_price',20,2)->nullable();
            $table->float('max_price',20,2)->nullable();
            $table->char('allowed_payment',20)->nullable();
            $table->char('coupon_rule',50)->nullable();
            $table->float('coupon_value',20)->nullable();
            $table->char('max_usage',10)->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('couponservices');
    }
};
