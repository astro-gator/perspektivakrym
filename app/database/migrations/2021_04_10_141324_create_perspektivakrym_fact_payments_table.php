<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymFactPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_fact_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('payment_id')->nullable();
            $table->string('type', 255);
            $table->date('date');
            $table->text('contractor')->nullable();
            $table->string('doc_number', 255)->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_fact_payments');
    }
}
