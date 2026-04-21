<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── payment_records ──────────────────────────────────────────────────
        // external_reference: every Kaspi webhook does a lookup by this field
        // status + paid_at: revenue reports and reconciliation admin queries
        Schema::table('payment_records', function (Blueprint $table) {
            if (!$this->hasIndex('payment_records', 'payment_records_external_reference_index')) {
                $table->index('external_reference');
            }
            if (!$this->hasIndex('payment_records', 'payment_records_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('payment_records', 'payment_records_status_paid_at_index')) {
                $table->index(['status', 'paid_at']);
            }
        });

        // ── olympiad_requests ────────────────────────────────────────────────
        // child_profile_id: quiz access checks, parent dashboard
        // (status, payment_status): admin "approved but not paid" filter
        // (status, created_at): trending / pagination
        Schema::table('olympiad_requests', function (Blueprint $table) {
            if (!$this->hasIndex('olympiad_requests', 'olympiad_requests_child_profile_id_index')) {
                $table->index('child_profile_id');
            }
            if (!$this->hasIndex('olympiad_requests', 'olympiad_requests_status_payment_status_index')) {
                $table->index(['status', 'payment_status']);
            }
            if (!$this->hasIndex('olympiad_requests', 'olympiad_requests_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });

        // ── quiz_results ─────────────────────────────────────────────────────
        // child_profile_id: child result history queries
        // submitted_at: leaderboard / time-range reports
        Schema::table('quiz_results', function (Blueprint $table) {
            if (!$this->hasIndex('quiz_results', 'quiz_results_child_profile_id_index')) {
                $table->index('child_profile_id');
            }
            if (!$this->hasIndex('quiz_results', 'quiz_results_submitted_at_index')) {
                $table->index('submitted_at');
            }
        });

        // ── users ─────────────────────────────────────────────────────────────
        // is_admin: admin recipient lookup in NotificationWorkflow
        // created_at: user list pagination in admin panel
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_is_admin_index')) {
                $table->index('is_admin');
            }
            if (!$this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ── questions ─────────────────────────────────────────────────────────
        // (quiz_id, position): questions load ordered by position on every quiz start
        Schema::table('questions', function (Blueprint $table) {
            if (!$this->hasIndex('questions', 'questions_quiz_id_position_index')) {
                $table->index(['quiz_id', 'position']);
            }
        });

        // ── answers ──────────────────────────────────────────────────────────
        // (question_id, position): answers load ordered by position alongside questions
        Schema::table('answers', function (Blueprint $table) {
            if (!$this->hasIndex('answers', 'answers_question_id_position_index')) {
                $table->index(['question_id', 'position']);
            }
        });

        // ── training_attempts ─────────────────────────────────────────────────
        // quiz_id: quiz popularity stats, attempt counts
        // (quiz_id, completed_at): completion rate analytics
        Schema::table('training_attempts', function (Blueprint $table) {
            if (!$this->hasIndex('training_attempts', 'training_attempts_quiz_id_index')) {
                $table->index('quiz_id');
            }
            if (!$this->hasIndex('training_attempts', 'training_attempts_quiz_id_completed_at_index')) {
                $table->index(['quiz_id', 'completed_at']);
            }
        });

        // ── child_profiles ────────────────────────────────────────────────────
        // (parent_id, created_at): parent dashboard — list children sorted by added date
        Schema::table('child_profiles', function (Blueprint $table) {
            if (!$this->hasIndex('child_profiles', 'child_profiles_parent_id_created_at_index')) {
                $table->index(['parent_id', 'created_at']);
            }
        });

        // ── quiz_categories ───────────────────────────────────────────────────
        // (quiz_id, sort_order): category list always ordered, loaded per quiz
        Schema::table('quiz_categories', function (Blueprint $table) {
            if (!$this->hasIndex('quiz_categories', 'quiz_categories_quiz_id_sort_order_index')) {
                $table->index(['quiz_id', 'sort_order']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropIndexIfExists('payment_records_external_reference_index');
            $table->dropIndexIfExists('payment_records_status_index');
            $table->dropIndexIfExists('payment_records_status_paid_at_index');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropIndexIfExists('olympiad_requests_child_profile_id_index');
            $table->dropIndexIfExists('olympiad_requests_status_payment_status_index');
            $table->dropIndexIfExists('olympiad_requests_status_created_at_index');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndexIfExists('quiz_results_child_profile_id_index');
            $table->dropIndexIfExists('quiz_results_submitted_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_is_admin_index');
            $table->dropIndexIfExists('users_created_at_index');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndexIfExists('questions_quiz_id_position_index');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropIndexIfExists('answers_question_id_position_index');
        });

        Schema::table('training_attempts', function (Blueprint $table) {
            $table->dropIndexIfExists('training_attempts_quiz_id_index');
            $table->dropIndexIfExists('training_attempts_quiz_id_completed_at_index');
        });

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->dropIndexIfExists('child_profiles_parent_id_created_at_index');
        });

        Schema::table('quiz_categories', function (Blueprint $table) {
            $table->dropIndexIfExists('quiz_categories_quiz_id_sort_order_index');
        });
    }

    protected function hasIndex(string $table, string $index): bool
    {
        try {
            return collect(Schema::getIndexes($table))
                ->contains(fn ($i) => $i['name'] === $index);
        } catch (\Throwable) {
            return false;
        }
    }
};
