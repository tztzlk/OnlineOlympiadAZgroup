<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
            $table->string('plan')->default('free')->after('is_admin');
        });

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        Schema::table('callback_requests', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
        });

        $this->fillPublicIds('users');
        $this->fillPublicIds('child_profiles');
        $this->fillPublicIds('olympiad_requests');
        $this->fillPublicIds('subjects');
        $this->fillPublicIds('quizzes');
        $this->fillPublicIds('quiz_results');
        $this->fillPublicIds('payment_records');
        $this->fillPublicIds('callback_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->unique('public_id');
        });

        Schema::table('callback_requests', function (Blueprint $table) {
            $table->unique('public_id');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn(['public_id', 'plan']);
        });
    }

    protected function fillPublicIds(string $table): void
    {
        DB::table($table)
            ->whereNull('public_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(fn ($row) => DB::table($table)->where('id', $row->id)->update(['public_id' => (string) Str::uuid()]));
    }
};
