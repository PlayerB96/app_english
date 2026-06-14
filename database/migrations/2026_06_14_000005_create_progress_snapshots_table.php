<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_track_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('practice_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level_estimated', 32);
            $table->decimal('accuracy_pct', 5, 2)->default(0);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->unsignedSmallInteger('streak_days')->default(0);
            $table->timestamp('snapshot_at')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'snapshot_at']);
            $table->index(['learning_track_id', 'snapshot_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_snapshots');
    }
};
