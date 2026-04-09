<?php

it('keeps user-facing olympiad pages and api messages free from mojibake', function () {
    $files = [
        resource_path('page/Subject.vue') => [
            'mustContain' => [
                'Оформление участия',
                'Оплатить через Kaspi',
                'Сохранить и перейти к оплате',
            ],
        ],
        resource_path('page/Training.vue') => [
            'mustContain' => [
                'Бесплатный тренировочный режим',
                'Проверить ответы',
            ],
        ],
        resource_path('page/Quiz.vue') => [
            'mustContain' => [
                'Завершить тест',
                'Попытка сброшена',
            ],
        ],
        app_path('Http/Controllers/Api/OlympiadRequestController.php') => [
            'mustContain' => [
                'Участие оформлено. Переходите к оплате.',
                'Статус оплаты обновлён.',
            ],
        ],
        app_path('Http/Controllers/Api/QuizController.php') => [
            'mustContain' => [
                'Заявка ещё не одобрена администратором.',
                'Оплата ещё не подтверждена.',
                'Попытка аннулирована.',
            ],
        ],
        app_path('Http/Controllers/Api/TrainingController.php') => [
            'mustContain' => [
                'Сначала создайте профиль ребёнка.',
                'Правильный ответ: ',
            ],
        ],
    ];

    foreach ($files as $path => $rules) {
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ($rules['mustContain'] as $expectedText) {
            expect($contents)->toContain($expectedText);
        }

        expect($contents)->not->toContain('Р Сџ');
        expect($contents)->not->toContain('РЎРѓРЎвЂљ');
    }
});
