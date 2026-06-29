<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('world_level_id');
            $table->unsignedTinyInteger('question_index');
            $table->string('type', 32);
            $table->string('difficulty', 16);
            $table->text('prompt');
            $table->text('context')->nullable();
            $table->json('options');
            $table->unsignedTinyInteger('correct_index');
            $table->timestamps();

            $table->unique(['world_level_id', 'question_index']);
            $table->index('world_level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_questions');
    }
};
