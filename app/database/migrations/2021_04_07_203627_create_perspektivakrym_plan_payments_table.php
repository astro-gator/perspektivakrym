<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymPlanPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_plan_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('deal_id')->index();
            //ДУ / земля / доп лот или подряд
            $table->string('type')->default('main');
            //первоначальный взнос (down_payment) или регулярный платеж (regular payment)
            $table->string('pay_type')->default('down_payment');
            $table->string('doc_number', 255)->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->date('date');
            $table->boolean('blocked')->default(0);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_plan_payments');
    }
}
