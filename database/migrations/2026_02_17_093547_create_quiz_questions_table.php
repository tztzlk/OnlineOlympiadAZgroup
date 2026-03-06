<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained('quiz_subjects')->onDelete('cascade');
    $table->text('question');
    $table->json('options'); // варианты ответов
    $table->string('answer'); // правильный ответ
    $table->string('image')->nullable(); // ссылка на картинку
    $table->string('audio')->nullable(); // ссылка на аудио
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
