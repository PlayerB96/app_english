<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_track_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['learning_track_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
