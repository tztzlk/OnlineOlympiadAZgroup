<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->boolean('is_published')->default(false)->after('time_limit');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('question');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->string('label', 1)->nullable()->after('question_id');
            $table->unsignedInteger('position')->default(0)->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['label', 'position']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_published']);
        });
    }
};
