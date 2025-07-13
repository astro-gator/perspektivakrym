<?php
/**
 * Соответсвие старых договоров сделкам
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerspektivakrymOldDocTable extends Migration
{
    public function up()
    {
        Schema::create('perspektivakrym_old_doc', function (Blueprint $table) {
            $table->increments('id');
            $table->string('doc')->nullable();
            $table->unsignedBigInteger('deal')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perspektivakrym_old_doc');
    }
}
