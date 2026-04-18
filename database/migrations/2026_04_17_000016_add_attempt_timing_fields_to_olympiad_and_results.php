<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->timestamp('attempt_started_at')->nullable()->after('disqualification_reason');
            $table->timestamp('attempt_last_activity_at')->nullable()->after('attempt_started_at');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('answers');
            $table->timestamp('submitted_at')->nullable()->after('started_at');
            $table->unsignedInteger('elapsed_seconds')->nullable()->after('submitted_at');
            $table->boolean('requires_review')->default(false)->after('elapsed_seconds');
            $table->json('review_reasons')->nullable()->after('requires_review');
            $table->index(['requires_review', 'created_at'], 'quiz_results_review_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex('quiz_results_review_created_idx');
            $table->dropColumn([
                'started_at',
                'submitted_at',
                'elapsed_seconds',
                'requires_review',
                'review_reasons',
            ]);
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropColumn([
                'attempt_started_at',
                'attempt_last_activity_at',
            ]);
        });
    }
};
