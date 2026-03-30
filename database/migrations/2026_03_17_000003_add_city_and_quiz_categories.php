<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city')->default('')->after('school');
        });

        Schema::create('quiz_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->unsignedTinyInteger('grade_from')->nullable();
            $table->unsignedTinyInteger('grade_to')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_category_id')->nullable()->after('quiz_id')->constrained('quiz_categories')->nullOnDelete();
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->foreignId('quiz_category_id')->nullable()->after('quiz_id')->constrained('quiz_categories')->nullOnDelete();
        });

        $quizIds = DB::table('quizzes')->pluck('id');

        foreach ($quizIds as $quizId) {
            $categoryId = DB::table('quiz_categories')->insertGetId([
                'quiz_id' => $quizId,
                'label' => 'Общая',
                'grade_from' => null,
                'grade_to' => null,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('questions')
                ->where('quiz_id', $quizId)
                ->update(['quiz_category_id' => $categoryId]);

            DB::table('quiz_results')
                ->where('quiz_id', $quizId)
                ->update(['quiz_category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quiz_category_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quiz_category_id');
        });

        Schema::dropIfExists('quiz_categories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
