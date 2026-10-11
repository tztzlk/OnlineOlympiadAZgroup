<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Файлы публичного диска для хостингов без постоянного диска (Vercel):
// картинки вопросов и предметов хранятся в базе и отдаются через /media/{path}.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->string('path', 512)->primary();
            // base64: одинаково переносимо между PostgreSQL, MySQL и SQLite
            $table->longText('contents');
            $table->string('mime_type', 120);
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
