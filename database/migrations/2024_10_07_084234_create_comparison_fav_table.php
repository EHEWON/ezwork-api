<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComparisonFavTable extends Migration
{
    public function up()
    {
        Schema::create('comparison_fav', function (Blueprint $table) {
            $table->id()->comment('唯一标识符');
            $table->integer('comparison_id')->comment('对照表id');
            $table->integer('customer_id')->comment('用户id');
            $table->timestamps(); // created_at 和 updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('comparison_fav');
    }
}