<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->index(['user_id', 'quiz_id'], 'quiz_results_user_quiz_idx');
            $table->index(['user_id', 'created_at'], 'quiz_results_user_created_idx');
            $table->index(['quiz_id', 'created_at'], 'quiz_results_quiz_created_idx');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->index(['user_id', 'subject_id'], 'olympiad_requests_user_subject_idx');
            $table->index(['user_id', 'status'], 'olympiad_requests_user_status_idx');
            $table->index(['subject_id', 'status', 'payment_status'], 'olympiad_requests_subject_status_payment_idx');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index(['subject_id', 'is_published'], 'quizzes_subject_published_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('quizzes_subject_published_idx');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropIndex('olympiad_requests_user_subject_idx');
            $table->dropIndex('olympiad_requests_user_status_idx');
            $table->dropIndex('olympiad_requests_subject_status_payment_idx');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex('quiz_results_user_quiz_idx');
            $table->dropIndex('quiz_results_user_created_idx');
            $table->dropIndex('quiz_results_quiz_created_idx');
        });
    }
};
