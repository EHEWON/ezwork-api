<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromptTable extends Migration
{
    public function up()
    {
        Schema::create('prompt', function (Blueprint $table) {
            $table->id()->comment('唯一标识符');
            $table->string('title')->comment('标题');
            $table->enum('share_flag', ['N', 'Y'])->default('N')->comment('分享状态。 N:不分享 Y:分享');
            $table->integer('added_count')->default(0)->comment('被添加的次数');
            $table->text('content')->comment('提示语内容');
            $table->integer('customer_id')->default(0)->comment('创建用户id');
            $table->timestamps(); // created_at 和 updated_at
            $table->enum('deleted_flag', ['N', 'Y'])->default('N');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prompt');
    }
}