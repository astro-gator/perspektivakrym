<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDealIdColumnFactPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('deal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->dropColumn('deal_id');
        });
    }
}
