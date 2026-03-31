<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an admin to login through the admin endpoint', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('secret123'),
        'is_admin' => true,
    ]);

    $this->postJson('/api/auth/admin/login', [
        'email' => 'admin@example.com',
        'password' => 'secret123',
    ])->assertOk()
        ->assertJsonPath('user.public_id', $admin->public_id)
        ->assertJsonPath('user.is_admin', true)
        ->assertJsonStructure(['token']);
});

it('rejects a non admin from the admin login endpoint', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('secret123'),
        'is_admin' => false,
    ]);

    $this->postJson('/api/auth/admin/login', [
        'email' => 'user@example.com',
        'password' => 'secret123',
    ])->assertForbidden();
});

it('returns dashboard metrics only for admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'users',
            'children',
            'quizzes',
            'results',
            'payments',
            'callbacks',
            'requests' => ['total', 'pending', 'approved', 'rejected'],
        ]);
});

it('keeps admin vue pages free from mojibake text regressions', function () {
    $files = [
        resource_path('page/admin/AdminLogin.vue') => [
            'mustContain' => [
                'Панель управления',
                'Войдите, чтобы продолжить',
                'Электронная почта',
                'Только для авторизованных сотрудников',
            ],
        ],
        resource_path('page/admin/AdminDashboard.vue') => [
            'mustContain' => [
                'Панель управления олимпиадами',
                'Актуальная статистика',
                'Статусы заявок',
                'Быстрые переходы',
            ],
        ],
        resource_path('page/admin/AdminLayout.vue') => [
            'mustContain' => [
                'Рабочая админ-панель',
                'Заявки',
                'Олимпиады',
                'Вернуться на сайт',
            ],
        ],
        resource_path('page/admin/AdminRequests.vue') => [
            'mustContain' => [
                'Заявки и оплата участников',
                'Статус заявки',
                'Подтвердить оплату',
                'Вернуть в ожидание',
            ],
        ],
    ];

    foreach ($files as $path => $rules) {
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ($rules['mustContain'] as $expectedText) {
            expect($contents)->toContain($expectedText);
        }

        expect($contents)->not->toContain('Рџ');
    }
});
