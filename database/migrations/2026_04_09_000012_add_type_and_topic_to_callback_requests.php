<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('email');
            $table->string('type')->default('callback')->after('message');
            $table->index(['type', 'status', 'created_at'], 'callback_requests_type_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table) {
            $table->dropIndex('callback_requests_type_status_created_idx');
            $table->dropColumn(['topic', 'type']);
        });
    }
};
