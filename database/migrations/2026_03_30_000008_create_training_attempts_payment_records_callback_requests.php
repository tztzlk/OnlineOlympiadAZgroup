<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_profile_id')->constrained('child_profiles')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_category_id')->nullable()->constrained('quiz_categories')->nullOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->json('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['child_profile_id', 'created_at'], 'training_attempts_child_created_idx');
        });

        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_profile_id')->nullable()->constrained('child_profiles')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('olympiad_request_id')->nullable()->constrained('olympiad_requests')->nullOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('KZT');
            $table->string('status')->default('pending');
            $table->string('external_reference')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status'], 'payment_records_parent_status_idx');
            $table->index(['child_profile_id', 'created_at'], 'payment_records_child_created_idx');
        });

        Schema::create('callback_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['status', 'created_at'], 'callback_requests_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_requests');
        Schema::dropIfExists('payment_records');
        Schema::dropIfExists('training_attempts');
    }
};
