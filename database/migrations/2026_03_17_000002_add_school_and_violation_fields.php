<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('school')->default('')->after('phone');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->timestamp('disqualified_at')->nullable()->after('completed');
            $table->string('disqualification_reason')->nullable()->after('disqualified_at');
        });
    }

    public function down(): void
    {
        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropColumn(['disqualified_at', 'disqualification_reason']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('school');
        });
    }
};
