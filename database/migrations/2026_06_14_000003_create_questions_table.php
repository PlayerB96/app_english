<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('learning_track_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->text('context')->nullable();
            $table->string('difficulty', 32)->default('beginner');
            $table->string('source', 32)->default('ai');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['learning_track_id', 'difficulty']);
            $table->index(['practice_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
