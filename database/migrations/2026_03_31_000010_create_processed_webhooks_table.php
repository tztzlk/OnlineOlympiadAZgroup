<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('event_id', 191);
            $table->string('event_type')->nullable();
            $table->string('payload_hash', 64);
            $table->string('status')->default('processed');
            $table->foreignId('olympiad_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_record_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
            $table->index(['provider', 'received_at']);
            $table->index(['payload_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_webhooks');
    }
};
