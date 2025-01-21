<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('translate', function (Blueprint $table) {
            //添加字段
            $table->unsignedBigInteger('prompt_id')->default(0)->comment('提示词ID');
            $table->unsignedBigInteger('comparison_id')->default(0)->comment('对照表ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('translate', function (Blueprint $table) {
            //
            $table->dropColumn(['prompt_id', 'comparison_id']);
        });
    }
};
