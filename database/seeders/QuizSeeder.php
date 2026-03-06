<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $mathId = DB::table('quiz_subjects')->insertGetId([
            'name' => 'Математика',
            'created_at'=>now(),
            'updated_at'=>now()
        ]);

        $englishId = DB::table('quiz_subjects')->insertGetId([
            'name' => 'Английский',
            'created_at'=>now(),
            'updated_at'=>now()
        ]);

        // Математика
        DB::table('quiz_questions')->insert([
            [
                'subject_id'=>$mathId,
                'question'=>'Найдите сумму 12 + 15',
                'options'=>json_encode(['25','27','30','28']),
                'answer'=>'27',
                'image'=>null,
                'audio'=>null,
                'created_at'=>now(),
                'updated_at'=>now()
            ],
            [
                'subject_id'=>$mathId,
                'question'=>'Найдите периметр квадрата со стороной 5',
                'options'=>json_encode(['10','15','20','25']),
                'answer'=>'20',
                'image'=>'square.png',
                'audio'=>null,
                'created_at'=>now(),
                'updated_at'=>now()
            ]
        ]);

        // Английский
        DB::table('quiz_questions')->insert([
            [
                'subject_id'=>$englishId,
                'question'=>"Выберите правильное слово: 'I ___ a cat.'",
                'options'=>json_encode(['am','is','are','be']),
                'answer'=>'am',
                'image'=>null,
                'audio'=>'i_am.mp3',
                'created_at'=>now(),
                'updated_at'=>now()
            ],
            [
                'subject_id'=>$englishId,
                'question'=>"Как будет 'яблоко' на английском?",
                'options'=>json_encode(['Apple','Orange','Banana','Grapes']),
                'answer'=>'Apple',
                'image'=>'apple.png',
                'audio'=>'apple.mp3',
                'created_at'=>now(),
                'updated_at'=>now()
            ]
        ]);
    }
}
