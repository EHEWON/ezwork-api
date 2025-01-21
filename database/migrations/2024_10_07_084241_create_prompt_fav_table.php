<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromptFavTable extends Migration
{
    public function up()
    {
        Schema::create('prompt_fav', function (Blueprint $table) {
            $table->id()->comment('唯一标识符');
            $table->integer('prompt_id')->comment('对照表id');
            $table->integer('customer_id')->comment('用户id');
            $table->timestamps(); // created_at 和 updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('prompt_fav');
    }
}