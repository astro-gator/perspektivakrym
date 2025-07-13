<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPlanPaymentsTable extends Migration
{
    /*
     * Поле для свойства платежа:
     *  - auto - добавлен автоматически
     *  - manual - добавлен в ручную
     *
     */
    public function up()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->string('add_type')->default('auto');
        });
    }

    public function down()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->dropColumn(['add_type']);
        });
    }
}
