<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('consumer_name');
            $table->string('document_type', 16);
            $table->string('document_number', 32);
            $table->string('address');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('item_type', 16);
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('complaint_type', 16);
            $table->text('description');
            $table->string('order_reference')->nullable();
            $table->string('status', 16)->default('pending');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
    }
};
