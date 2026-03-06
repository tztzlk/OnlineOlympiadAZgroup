<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizQuestion;

class QuizQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        QuizQuestion::create([
            'question' => 'Сколько будет 2 + 2?',
            'options' => json_encode(['2','3','4','5']),
            'answer' => '4'
        ]);

        QuizQuestion::create([
            'question' => 'Столица Франции?',
            'options' => json_encode(['Берлин','Париж','Лондон','Рим']),
            'answer' => 'Париж'
        ]);
    }
}
