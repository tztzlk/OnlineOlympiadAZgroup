<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('image_source', 20)->nullable()->after('question');
            $table->text('image_url')->nullable()->after('image_source');
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['image_source', 'image_url', 'image_path']);
        });
    }
};
