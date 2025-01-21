<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComparisonTable extends Migration
{
    public function up()
    {
        Schema::create('comparison', function (Blueprint $table) {
            $table->id()->comment('唯一标识符');
            $table->string('title')->comment('标题');
            $table->string('origin_lang', 32)->comment('源语言');
            $table->string('target_lang', 32)->comment('对照语种');
            $table->enum('share_flag', ['N', 'Y'])->default('N')->comment('分享状态。 N:不分享 Y:分享');
            $table->integer('added_count')->default(0)->comment('被添加的次数');
            $table->text('content')->comment('术语，源1,目标1;源2,目标2');
            $table->integer('customer_id')->default(0)->comment('创建用户id');
            $table->timestamps(); // created_at 和 updated_at
            $table->enum('deleted_flag', ['N', 'Y'])->default('N');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comparison');
    }
}