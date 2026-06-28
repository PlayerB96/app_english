<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tier_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 32);
            $table->string('tier', 32);
            $table->unsignedTinyInteger('reset_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'mode', 'tier']);
            $table->index(['user_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tier_resets');
    }
};
