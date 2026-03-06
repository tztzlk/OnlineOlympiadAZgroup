<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('olympiad_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date'); 
            $table->string('grade');
            $table->string('language', 10);
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('parent_email');
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->boolean('completed')->default(false);
         
    $table->enum('payment_status', ['pending','paid','failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olympiad_requests');
    }
};
