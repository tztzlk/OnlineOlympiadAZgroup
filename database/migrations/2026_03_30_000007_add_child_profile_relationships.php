<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->foreignId('child_profile_id')->nullable()->after('user_id')->constrained('child_profiles')->nullOnDelete();
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->foreignId('child_profile_id')->nullable()->after('user_id')->constrained('child_profiles')->nullOnDelete();
        });

        $requests = DB::table('olympiad_requests')->orderBy('id')->get();

        foreach ($requests as $request) {
            $profileId = DB::table('child_profiles')
                ->where('parent_id', $request->user_id)
                ->where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->value('id');

            if (!$profileId) {
                $profileId = DB::table('child_profiles')->insertGetId([
                    'parent_id' => $request->user_id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'birth_date' => $request->birth_date,
                    'grade' => is_numeric($request->grade) ? (int) $request->grade : null,
                    'school' => DB::table('users')->where('id', $request->user_id)->value('school'),
                    'city' => DB::table('users')->where('id', $request->user_id)->value('city'),
                    'language_preference' => $request->language ?: 'ru',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('olympiad_requests')
                ->where('id', $request->id)
                ->update(['child_profile_id' => $profileId]);

            DB::table('quiz_results')
                ->where('user_id', $request->user_id)
                ->where('quiz_id', DB::table('quizzes')->where('subject_id', $request->subject_id)->value('id'))
                ->whereNull('child_profile_id')
                ->update(['child_profile_id' => $profileId]);
        }
    }

    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('child_profile_id');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('child_profile_id');
        });
    }
};
