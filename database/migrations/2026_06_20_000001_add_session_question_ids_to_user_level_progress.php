<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_level_progress', function (Blueprint $table) {
            $table->json('session_question_ids')->nullable()->after('correct_question_ids');
        });
    }

    public function down(): void
    {
        Schema::table('user_level_progress', function (Blueprint $table) {
            $table->dropColumn('session_question_ids');
        });
    }
};
