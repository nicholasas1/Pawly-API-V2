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
        Schema::table('orderservices', function (Blueprint $table) {
            //
            $table->char('doctor_id',20)->nullable()->after('type');
            $table->char('service_id',20)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orderservices', function (Blueprint $table) {
            //
        });
    }
};
