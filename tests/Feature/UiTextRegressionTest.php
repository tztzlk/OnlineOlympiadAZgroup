<?php

it('keeps user-facing olympiad pages and api messages free from mojibake', function () {
    $files = [
        resource_path('page/Subject.vue') => [
            'mustContain' => [
                'Оформление участия',
                'Ожидаем оплату',
                'Платёж отмечен, идёт автосверка',
                'Автосверка не завершилась, нужна проверка',
                'Оплата подтверждена, доступ открыт',
            ],
        ],
        resource_path('page/Training.vue') => [
            'mustContain' => [
                'Бесплатный тренировочный режим',
                'Ваш ответ',
                'Правильный ответ',
                'Разбор',
            ],
        ],
        resource_path('page/RequestSuccess.vue') => [
            'mustContain' => [
                'Автосверка',
                'Оплата подтверждена, доступ открыт',
            ],
        ],
        resource_path('page/Waiting.vue') => [
            'mustContain' => [
                'Платёж отмечен, идёт автосверка',
                'Оплата подтверждена, доступ открыт',
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
                "'selected_answer' =>",
                "'correct_answer' =>",
            ],
        ],
    ];

    foreach ($files as $path => $rules) {
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ($rules['mustContain'] as $expectedText) {
            expect($contents)->toContain($expectedText);
        }

        expect($contents)->not->toContain("\u{FFFD}");
    }
});
