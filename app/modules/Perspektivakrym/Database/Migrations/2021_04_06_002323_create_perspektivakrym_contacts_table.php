<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymContactsTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('deal_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->text('contact_full_name')->nullable();
            $table->text('contact_email')->nullable();
            $table->text('contact_phones')->nullable();
            $table->unsignedBigInteger('unisender_list_id')->nullable();
            $table->string('status')->default('wait');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_contacts');
    }
}
