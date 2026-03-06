<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
class SubjectSeeder extends Seeder
{
    
   
    public function run(): void
    {
        Subject::create([
    'name' => 'Математика',
    'description' => 'Логика, задачи, геометрия и вычисления.',
    'image' => 'https://cdn-icons-png.flaticon.com/512/2103/2103633.png',
    'start_date' => '2026-03-14',
]);

Subject::create([
    'name' => 'Английский язык',
    'description' => 'Грамматика, чтение и словарный запас.',
    'image' => 'https://cdn-icons-png.flaticon.com/512/484/484582.png',
    'start_date' => '2026-03-28',
]);
    }
}
