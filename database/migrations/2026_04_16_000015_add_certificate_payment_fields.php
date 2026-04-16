<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->decimal('certificate_price', 10, 2)->default(600)->after('answers');
            $table->string('certificate_payment_status')->default('unpaid')->after('certificate_price');
            $table->timestamp('certificate_paid_at')->nullable()->after('certificate_payment_status');

            $table->index(['certificate_payment_status', 'created_at'], 'quiz_results_certificate_status_created_idx');
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->foreignId('quiz_result_id')->nullable()->after('olympiad_request_id')->constrained('quiz_results')->nullOnDelete();
            $table->string('payment_type')->default('olympiad')->after('currency');

            $table->index(['payment_type', 'status'], 'payment_records_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropIndex('payment_records_type_status_idx');
            $table->dropConstrainedForeignId('quiz_result_id');
            $table->dropColumn('payment_type');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropIndex('quiz_results_certificate_status_created_idx');
            $table->dropColumn([
                'certificate_price',
                'certificate_payment_status',
                'certificate_paid_at',
            ]);
        });
    }
};
