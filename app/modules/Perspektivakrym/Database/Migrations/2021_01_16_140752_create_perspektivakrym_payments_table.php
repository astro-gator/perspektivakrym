<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('deal_id'); //ID сделки в Б24
            $table->unsignedInteger('type'); //тип документа 1 - за землю, паркинга или кладовки, 2 - за подряд
            $table->string('doc_number')->nullable(); //номер документа
            $table->unsignedInteger('number'); //порядковый номер платежа
            $table->string('guid')->nullable(); //guid платежа из 1С
            $table->date('fact_date')->nullable(); //фактическая дата оплаты
            $table->date('plan_date')->nullable(); //плановая дата оплаты
            $table->unsignedBigInteger('fact_amount')->default(0); //фактическая сумма оплаты
            $table->unsignedBigInteger('plan_amount')->default(0); //плановая сумма оплаты
            $table->string('status')->nullable(); //статус
            $table->text('note')->nullable(); //примечание

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_payments');
    }
}
