<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPerspektivakrymPlanPaymentsNumberGraphField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->unsignedInteger('number_graph')->default(0);
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
            $table->dropColumn('number_graph');
        });
    }
}
