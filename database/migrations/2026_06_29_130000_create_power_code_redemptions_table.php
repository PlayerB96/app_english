<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('internal_code', 16);
            $table->string('public_code', 3);
            $table->unsignedInteger('power_amount');
            $table->timestamps();

            $table->unique(['user_id', 'internal_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_code_redemptions');
    }
};
