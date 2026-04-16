<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->string('provider')->default('kaspi');
            $table->string('reconciliation_status')->default('awaiting_payment');
            $table->timestamp('customer_reported_at')->nullable();
            $table->timestamp('customer_paid_at')->nullable();

            $table->index(['provider', 'reconciliation_status'], 'payment_records_provider_reconciliation_idx');
        });

        Schema::create('payment_import_rows', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('kaspi');
            $table->string('fingerprint')->unique();
            $table->string('external_reference')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('comment')->nullable();
            $table->json('raw_payload')->nullable();
            $table->foreignId('matched_payment_record_id')->nullable()->constrained('payment_records')->nullOnDelete();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['provider', 'status'], 'payment_import_rows_provider_status_idx');
            $table->index(['paid_at', 'status'], 'payment_import_rows_paid_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_import_rows');

        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropIndex('payment_records_provider_reconciliation_idx');
            $table->dropColumn([
                'provider',
                'reconciliation_status',
                'customer_reported_at',
                'customer_paid_at',
            ]);
        });
    }
};
