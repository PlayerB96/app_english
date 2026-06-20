<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_level_progress', function (Blueprint $table) {
            $table->json('correct_question_ids')->nullable()->after('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('user_level_progress', function (Blueprint $table) {
            $table->dropColumn('correct_question_ids');
        });
    }
};
