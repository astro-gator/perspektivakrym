<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymParseDealsTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_parse_deals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('distribution_list');
            $table->unsignedBigInteger('deal_id')->nullable();
            $table->json('deal')->nullable();
            $table->json('contact')->nullable();
            $table->string('status')->default('wait_get_contact');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_parse_deals');
    }
}
