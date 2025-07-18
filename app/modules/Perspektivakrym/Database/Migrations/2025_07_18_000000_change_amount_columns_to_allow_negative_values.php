<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountColumnsToAllowNegativeValues extends Migration
{
    public function up()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->bigInteger('amount')->default(0)->change();
        });

        Schema::table('perspektivakrym_fact_payments', function (Blueprint $table) {
            $table->bigInteger('amount')->default(0)->change();
        });

        Schema::table('perspektivakrym_payments', function (Blueprint $table) {
            $table->bigInteger('fact_amount')->default(0)->change();
            $table->bigInteger('plan_amount')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->default(0)->change();
        });

        Schema::table('perspektivakrym_fact_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->default(0)->change();
        });

        Schema::table('perspektivakrym_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('fact_amount')->default(0)->change();
            $table->unsignedBigInteger('plan_amount')->default(0)->change();
        });
    }
}
