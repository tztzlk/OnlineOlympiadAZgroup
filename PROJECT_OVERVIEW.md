# Проект: OnlineOlympiad AZgroup

## Технический стек

| Слой | Технология |
|------|-----------|
| Backend | Laravel 12 (PHP 8.2) |
| Frontend | Vue 3 + Vite |
| База данных | MySQL |
| Авторизация | Laravel Sanctum (Token-based) |
| Платежи | Kaspi, Stripe, Yookassa |
| Кеш / очереди | Redis (вероятно) |

## Назначение

Платформа для проведения онлайн олимпиад:

- Регистрация участников (родитель + профиль ребёнка)
- Оплата участия (несколько платёжных систем)
- Прохождение тестов (квизы с категориями по классам)
- Тренировочный режим
- Рейтинговая таблица (leaderboard)
- Административная панель с ролями доступа

## Архитектура

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AdminController.php          — Импорт, управление, дашборд
│   │   ├── OlympiadRequestController.php — Заявки на участие
│   │   ├── QuizController.php           — Прохождение тестов
│   │   ├── TrainingController.php       — Тренировочный режим
│   │   ├── LeaderboardController.php    — Рейтинг
│   │   ├── ProfileController.php        — Профиль пользователя
│   │   └── WebhookController.php        — Обработка вебхуков оплаты
│   └── Middleware/
│       └── VerifyWebhookSignature.php   — Верификация вебхуков
├── Models/
│   ├── User.php / ChildProfile.php
│   ├── OlympiadRequest.php
│   ├── QuizResult.php / TrainingAttempt.php
│   └── PaymentRecord.php
└── Support/
    └── ProofOfWorkService.php           — Anti-bot защита
resources/
└── components/
    └── HowItWorks.vue / ...             — Vue компоненты
database/
└── migrations/
    └── 2026_03_24_000005_add_production_indexes.php
```

## Основные роли

- **Гость** — просмотр олимпиад
- **Родитель** — регистрация, оплата, просмотр результатов
- **Администратор** — управление заявками, импорт, дашборд

## Система безопасности (что есть)

- Laravel Sanctum для авторизации API
- Верификация подписей вебхуков (HMAC)
- CORS настройки
- Rate limiting
- Proof-of-Work anti-bot защита
- HTTPS enforcement (конфигурируется через ENV)
