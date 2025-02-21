<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('translate', function (Blueprint $table) {
            $table->string('rand_user_id', 64)->default(null)->comment('随机用户ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('translate', function (Blueprint $table) {
            //
        });
    }
};
