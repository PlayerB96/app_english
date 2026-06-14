<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_session_id')->constrained()->cascadeOnDelete();
            $table->text('response_text');
            $table->boolean('is_correct')->nullable();
            $table->text('feedback')->nullable();
            $table->string('input_mode', 16)->default('text');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['practice_session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
