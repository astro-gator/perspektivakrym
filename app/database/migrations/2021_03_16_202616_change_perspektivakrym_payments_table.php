<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePerspektivakrymPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('perspektivakrym_payments', function (Blueprint $table) {
            $table->boolean('blocked')->default(0);
            $table->unsignedBigInteger('user_block')->nullable();
        });
    }

    public function down()
    {
        Schema::table('perspektivakrym_payments', function (Blueprint $table) {
            $table->dropColumn(['blocked', 'user_block']);
        });
    }
}
