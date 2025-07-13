<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeDateTextDateColumnsInPlanPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->boolean('is_text_date')->default(0);
            $table->text('text_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->dropColumn(['is_text_date', 'text_date']);
        });
    }
}
