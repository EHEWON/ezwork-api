<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('translate_logs', function (Blueprint $table) {
            $table->bigIncrements('id',20)->comment('唯一标识符');
            $table->string('md5_key', 100)->default(null)->comment('标题');
            $table->text('source')->default(null)->comment('原内容');
            $table->text('content')->default(null)->comment('目标内容');
            $table->string('target_lang', 32)->default(null)->comment('目标语言');
            $table->string('model', 255)->default(null)->comment('翻译模型');
            $table->string('backup_model', 255)->default(null)->comment('备用模型');
            $table->string('prompt', 1024)->default(null)->comment('ai翻译提示语');
            $table->string('api_url', 255)->default(null)->comment('接口地址');
            $table->string('api_key', 255)->default(null)->comment('接口key');
            $table->bigInteger('word_count', false,false)->default(0)->comment('字数');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('translate_logs');
    }
};
