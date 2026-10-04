<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Шифрует персональные данные детей и родителей, которые не участвуют в поиске:
 * дату рождения ребёнка, телефон и email родителя в заявке.
 * Зашифрованная строка длиннее исходной, поэтому колонки переводятся в text.
 */
return new class extends Migration
{
    protected array $columns = [
        'child_profiles' => ['birth_date'],
        'olympiad_requests' => ['birth_date', 'parent_phone', 'parent_email'],
    ];

    public function up(): void
    {
        Schema::table('child_profiles', function (Blueprint $table) {
            $table->text('birth_date')->nullable()->change();
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->text('birth_date')->nullable()->change();
            $table->text('parent_phone')->change();
            $table->text('parent_email')->change();
        });

        $this->transform(fn (string $value) => $this->isEncrypted($value) ? $value : Crypt::encryptString($value));
    }

    public function down(): void
    {
        $this->transform(fn (string $value) => $this->isEncrypted($value) ? Crypt::decryptString($value) : $value);

        Schema::table('child_profiles', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
        });

        Schema::table('olympiad_requests', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->string('parent_phone')->change();
            $table->string('parent_email')->change();
        });
    }

    protected function transform(callable $callback): void
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)->select(['id', ...$columns])->orderBy('id')->chunkById(500, function ($rows) use ($table, $columns, $callback) {
                foreach ($rows as $row) {
                    $changes = [];

                    foreach ($columns as $column) {
                        $value = $row->{$column};

                        if ($value === null || $value === '') {
                            continue;
                        }

                        $changes[$column] = $callback((string) $value);
                    }

                    if ($changes) {
                        DB::table($table)->where('id', $row->id)->update($changes);
                    }
                }
            });
        }
    }

    protected function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
