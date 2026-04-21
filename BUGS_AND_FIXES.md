# Баги, уязвимости и исправления

> Дата анализа: 2026-04-22

---

## Легенда приоритетов

| Метка | Уровень |
|-------|---------|
| 🔴 КРИТИЧНО | Уязвимость безопасности или потеря данных |
| 🟠 ВЫСОКИЙ | Логический баг, может навредить бизнесу |
| 🟡 СРЕДНИЙ | Качество кода, потенциальные проблемы |
| 🟢 НИЗКИЙ | Производительность, рефакторинг |

---

## 🔴 КРИТИЧНО — Отсутствие авторизации в DELETE

**Файл:** `app/Http/Controllers/Api/OlympiadRequestController.php`

**Проблема:**  
Метод `destroy()` удаляет запись участия **без проверки прав администратора**. Любой авторизованный пользователь может удалить любую заявку, зная её ID.

```php
// ❌ КАК ЕСТЬ — нет проверки прав
public function destroy(OlympiadRequest $olympiadRequest)
{
    $olympiadRequest->delete();

    return response()->json([
        'message' => 'Запись участия удалена.',
    ]);
}
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
public function destroy(Request $request, OlympiadRequest $olympiadRequest)
{
    $user = $request->user();

    if (!$user || !$user->hasAdminCapability('requests')) {
        return response()->json(['message' => 'Недостаточно прав.'], 403);
    }

    $olympiadRequest->delete();

    return response()->json(['message' => 'Запись участия удалена.']);
}
```

---

## 🔴 КРИТИЧНО — Обход отклонения заявки

**Файл:** `app/Http/Controllers/Api/OlympiadRequestController.php` (строки 60–81)

**Проблема:**  
При повторной подаче заявки система автоматически устанавливает `status = 'approved'`, даже если заявка была **отклонена** администратором. Пользователь может обойти отказ.

```php
// ❌ КАК ЕСТЬ — переопределяет rejected → approved
if ($existing) {
    $existing->update([
        ...$payload,
        'status' => 'approved',
    ]);
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
if ($existing) {
    if ($existing->status === 'rejected') {
        return response()->json([
            'message' => 'Заявка была отклонена. Свяжитесь с поддержкой для повторной подачи.'
        ], 403);
    }

    $existing->update([
        ...$payload,
        'status' => $existing->status === 'pending' ? 'pending' : $existing->status,
        'payment_status' => $existing->payment_status === 'paid' ? 'paid' : 'pending',
    ]);
```

---

## 🟠 ВЫСОКИЙ — Неправильная дата рождения по умолчанию

**Файл:** `app/Http/Controllers/Api/OlympiadRequestController.php` (строка 44)

**Проблема:**  
Если `birth_date` не передана и у ребёнка её нет — подставляется `now()` (текущая дата). Это логически неверно.

```php
// ❌ КАК ЕСТЬ
$birthDate = $data['birth_date'] ?? optional($child->birth_date)->toDateString() ?? now()->toDateString();
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
$birthDate = $data['birth_date']
    ?? optional($child->birth_date)->toDateString();

abort_if($birthDate === null, 422, 'Требуется дата рождения участника');
```

---

## 🟠 ВЫСОКИЙ — Race condition в Proof of Work

**Файл:** `app/Support/ProofOfWorkService.php` (строки 57–58)

**Проблема:**  
`Cache::add()` не является полностью атомарным в конкурентной среде — между проверкой и установкой возможна race condition. Один токен может быть использован дважды.

```php
// ❌ КАК ЕСТЬ
$cacheKey = 'pow:' . hash('sha256', $token . '|' . $nonce);
abort_unless(Cache::add($cacheKey, true, now()->addMinutes(15)), 422, 'Токен уже использован.');
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ — атомарная блокировка
$cacheKey = 'pow:' . hash('sha256', $token . '|' . $nonce);
$lockKey  = 'pow_lock:' . $cacheKey;

Cache::lock($lockKey, 5)->get(function () use ($cacheKey) {
    abort_if(Cache::has($cacheKey), 422, 'Этот anti-bot токен уже использован.');
    Cache::put($cacheKey, true, now()->addMinutes(15));
});
```

---

## 🟠 ВЫСОКИЙ — Падение при некорректном CSV

**Файл:** `app/Http/Controllers/Api/AdminController.php` (строка ~223)

**Проблема:**  
`array_combine()` возвращает `false`, если количество колонок не совпадает с заголовками. Последующий доступ к `$row['parent_email']` вызовет fatal error.

```php
// ❌ КАК ЕСТЬ
$row = array_combine($header, $columns);
$email = trim((string) ($row['parent_email'] ?? ''));
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
$row = array_combine($header, array_slice($columns, 0, count($header)));

if ($row === false) {
    $errors[] = [
        'line'    => $lineNumber + 1,
        'message' => 'Некорректный формат CSV: количество колонок не совпадает с заголовками',
    ];
    continue;
}

$email = trim((string) ($row['parent_email'] ?? ''));
```

---

## 🟡 СРЕДНИЙ — XSS через v-html

**Файл:** `resources/components/HowItWorks.vue` (строка 22)

**Проблема:**  
`v-html` рендерит HTML без санитизации. Сейчас там SVG-иконки из кода, но если данные будут приходить с бэкенда — возможна XSS-атака.

```vue
<!-- ❌ КАК ЕСТЬ -->
<div class="step-card__icon" v-html="step.icon"></div>
```

**Исправление:**
```vue
<!-- ✅ КАК ДОЛЖНО БЫТЬ — использовать компоненты -->
<component :is="step.iconComponent" class="step-card__icon" />
```

Или если иконки всегда статические — оставить как есть, но **никогда** не передавать туда данные от пользователей.

---

## 🟡 СРЕДНИЙ — Незаполненные webhook-секреты

**Файл:** `.env.example` (строки 95–97)

**Проблема:**  
Webhook-секреты пустые по умолчанию. Если в production забыть заполнить — верификация отключится.

```env
# ❌ КАК ЕСТЬ — пустые значения
YOOKASSA_WEBHOOK_SECRET=
STRIPE_WEBHOOK_SECRET=
TELEGRAM_WEBHOOK_SECRET_TOKEN=
```

**Исправление:**  
Добавить проверку при загрузке приложения (в `AppServiceProvider`):

```php
// ✅ В AppServiceProvider::boot()
if (app()->isProduction()) {
    foreach (['YOOKASSA_WEBHOOK_SECRET', 'STRIPE_WEBHOOK_SECRET'] as $key) {
        if (empty(env($key))) {
            throw new \RuntimeException("Не задан обязательный ENV: {$key}");
        }
    }
}
```

---

## 🟡 СРЕДНИЙ — Нет обработки исключений в WebhookController

**Файл:** `app/Http/Controllers/Api/WebhookController.php` (строка ~35)

**Проблема:**  
Если `normalizeWebhook()` выбросит исключение — Laravel вернёт 500 без логирования контекста. Платёжная система будет повторять запросы.

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
protected function handleWebhook(Request $request, string $provider): JsonResponse
{
    try {
        $payload    = $request->json()->all();
        $normalized = $this->normalizeWebhook($provider, $payload);

        $processed = DB::transaction(function () use ($normalized, $payload) {
            // ...
        });

        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        Log::channel('security')->error('webhook.error', [
            'provider' => $provider,
            'error'    => $e->getMessage(),
            'ip'       => $request->ip(),
        ]);

        return response()->json(['message' => 'Ошибка обработки'], 500);
    }
}
```

---

## 🟡 СРЕДНИЙ — Нет логирования административных действий

**Файл:** `app/Http/Controllers/Api/AdminController.php`

**Проблема:**  
Импорт участников и платежей не логирует кто, когда и что импортировал. Невозможно аудировать действия администраторов.

**Исправление:**
```php
// ✅ Добавить в конец importPayments() и importRequests()
Log::channel('security')->info('admin.import', [
    'admin_id'        => $request->user()->id,
    'action'          => 'import_payments',
    'imported_count'  => $result['imported'],
    'duplicate_count' => $result['duplicates'],
    'ip'              => $request->ip(),
]);
```

---

## 🟢 НИЗКИЙ — Группировка по subjects.name вместо subjects.id

**Файл:** `app/Http/Controllers/Api/AdminController.php` (строки 100–112)

**Проблема:**  
`GROUP BY subjects.name` медленнее и некорректно при наличии двух предметов с одинаковым именем.

```php
// ❌ КАК ЕСТЬ
->groupBy('subjects.name')
```

**Исправление:**
```php
// ✅ КАК ДОЛЖНО БЫТЬ
->selectRaw('subjects.id, subjects.name, COUNT(...) as results_count, ...')
->groupBy('subjects.id', 'subjects.name')
```

---

## 🟢 НИЗКИЙ — Дублирование логики resolveChild в контроллерах

**Файлы:**
- `app/Http/Controllers/Api/QuizController.php`
- `app/Http/Controllers/Api/TrainingController.php`
- `app/Http/Controllers/Api/OlympiadRequestController.php`

**Проблема:**  
Логика получения профиля ребёнка дублируется в трёх местах. При изменении — нужно обновлять везде.

**Исправление:** Создать трейт `ResolvesChildProfile`:

```php
// app/Http/Controllers/Concerns/ResolvesChildProfile.php
trait ResolvesChildProfile
{
    protected function resolveChildProfile(Request $request, int $parentId): ChildProfile
    {
        $childId = $request->input('child_profile_id');

        if ($childId) {
            return ChildProfile::where('parent_id', $parentId)
                ->where('public_id', $childId)
                ->firstOrFail();
        }

        return ChildProfile::where('parent_id', $parentId)->firstOrFail();
    }
}
```

---

## Итоговая таблица

| # | Файл | Приоритет | Проблема |
|---|------|-----------|----------|
| 1 | `OlympiadRequestController.php` | 🔴 | Нет авторизации в DELETE |
| 2 | `OlympiadRequestController.php` | 🔴 | Обход отклонения заявки |
| 3 | `OlympiadRequestController.php` | 🟠 | Неверная дата рождения по умолчанию |
| 4 | `ProofOfWorkService.php` | 🟠 | Race condition в anti-bot |
| 5 | `AdminController.php` | 🟠 | Падение при некорректном CSV |
| 6 | `HowItWorks.vue` | 🟡 | XSS через v-html |
| 7 | `.env.example` | 🟡 | Пустые webhook-секреты |
| 8 | `WebhookController.php` | 🟡 | Нет try/catch в webhook |
| 9 | `AdminController.php` | 🟡 | Нет аудит-лога действий |
| 10 | `AdminController.php` | 🟢 | GROUP BY по name вместо id |
| 11 | 3 контроллера | 🟢 | Дублирование resolveChild |

---

## Что фиксить первым (порядок действий)

1. **Сейчас, до production:**  
   - Баги #1 и #2 — критичные уязвимости безопасности

2. **В ближайшем спринте:**  
   - Баги #3, #4, #5 — логические ошибки

3. **Технический долг:**  
   - Баги #6–#11 — качество кода и производительность
