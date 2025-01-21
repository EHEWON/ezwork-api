<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDoc2xFlagToTranslateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('translate', function (Blueprint $table) {
            $table->enum('doc2x_flag', ['Y', 'N'])->default('N')->comment('是否使用doc2x转换pdf');
            $table->string('doc2x_secret_key', 32)->default('')->comment('doc2x的api密钥');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translate', function (Blueprint $table) {
            $table->dropColumn(['doc2x_flag', 'doc2x_secret_key']);
        });
    }
}