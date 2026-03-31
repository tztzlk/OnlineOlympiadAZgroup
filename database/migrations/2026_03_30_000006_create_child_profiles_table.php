<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('grade')->nullable();
            $table->string('school')->nullable();
            $table->string('city')->nullable();
            $table->string('language_preference', 10)->default('ru');
            $table->timestamps();

            $table->index(['parent_id', 'last_name'], 'child_profiles_parent_last_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profiles');
    }
};
